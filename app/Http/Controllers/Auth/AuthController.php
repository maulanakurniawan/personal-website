<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const LOGIN_MAX_ATTEMPTS = 3;

    private const LOGIN_BASE_LOCKOUT_MINUTES = 5;

    private const SIGNUP_MAX_ATTEMPTS = 5;

    private const SIGNUP_DECAY_SECONDS = 300;

    public function showSignup(): View
    {
        if (! config('auth.signup_enabled')) {
            return view('auth.signup_disabled');
        }

        return view('auth.signup');
    }

    public function signup(Request $request): RedirectResponse
    {
        if (! config('auth.signup_enabled')) {
            return redirect()->route('signup');
        }

        $signupLimiterKey = $this->signupLimiterKey($request);

        if (RateLimiter::tooManyAttempts($signupLimiterKey, self::SIGNUP_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($signupLimiterKey);

            return back()->withErrors([
                'email' => 'Too many signup attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
            ])->onlyInput('name', 'email');
        }

        RateLimiter::hit($signupLimiterKey, self::SIGNUP_DECAY_SECONDS);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'cf-turnstile-response' => ['required', 'string'],
        ]);

        $secretKey = config('services.turnstile.secret_key');

        if (empty($secretKey)) {
            return back()
                ->withErrors(['turnstile' => 'Turnstile is not configured. Please try again later.'])
                ->onlyInput('name', 'email');
        }

        $verification = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $validated['cf-turnstile-response'],
            'remoteip' => $request->ip(),
        ]);

        if (! $verification->successful() || ! data_get($verification->json(), 'success')) {
            return back()
                ->withErrors(['turnstile' => 'Please complete Turnstile verification and try again.'])
                ->onlyInput('name', 'email');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'plan' => null,
        ]);
        Auth::login($user);

        $user->sendEmailVerificationNotification();

        RateLimiter::clear($signupLimiterKey);

        return redirect()->route('verification.notice');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = PasswordBroker::sendResetLink(
            $request->only('email')
        );

        if ($status === PasswordBroker::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $lockoutSecondsRemaining = $this->loginLockoutSecondsRemaining($request);

        if ($lockoutSecondsRemaining > 0) {
            return back()->withErrors([
                'email' => 'Too many failed login attempts. Please try again in ' . ceil($lockoutSecondsRemaining / 60) . ' minute(s).',
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->registerFailedLoginAttempt($request);

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $this->clearLoginAttemptData($request);

        $request->session()->regenerate();

        return redirect()->intended($this->intendedRouteForAuthenticatedUser());
    }

    private function intendedRouteForAuthenticatedUser(): string
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return route('dashboard');
        }

        return route('verification.notice');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function signupLimiterKey(Request $request): string
    {
        return 'signup-attempts:' . Str::lower($request->input('email', 'unknown')) . '|' . $request->ip();
    }

    private function loginAttemptKey(Request $request): string
    {
        return 'login-attempts:' . Str::lower($request->input('email', 'unknown')) . '|' . $request->ip();
    }

    private function loginLockoutKey(Request $request): string
    {
        return 'login-lockout:' . Str::lower($request->input('email', 'unknown')) . '|' . $request->ip();
    }

    private function loginLockoutCountKey(Request $request): string
    {
        return 'login-lockout-count:' . Str::lower($request->input('email', 'unknown')) . '|' . $request->ip();
    }

    private function loginLockoutSecondsRemaining(Request $request): int
    {
        $blockedUntil = Cache::get($this->loginLockoutKey($request));

        if (! is_int($blockedUntil) || $blockedUntil <= now()->timestamp) {
            return 0;
        }

        return $blockedUntil - now()->timestamp;
    }

    private function registerFailedLoginAttempt(Request $request): void
    {
        $attemptKey = $this->loginAttemptKey($request);
        $attempts = Cache::increment($attemptKey);
        Cache::put($attemptKey, $attempts, now()->addDay());

        if ($attempts < self::LOGIN_MAX_ATTEMPTS) {
            return;
        }

        Cache::forget($attemptKey);

        $lockoutCountKey = $this->loginLockoutCountKey($request);
        $lockoutCount = Cache::increment($lockoutCountKey);
        Cache::put($lockoutCountKey, $lockoutCount, now()->addDays(30));

        $lockoutMinutes = self::LOGIN_BASE_LOCKOUT_MINUTES * ($lockoutCount ** 2);
        Cache::put($this->loginLockoutKey($request), now()->addMinutes($lockoutMinutes)->timestamp, now()->addMinutes($lockoutMinutes));
    }

    private function clearLoginAttemptData(Request $request): void
    {
        Cache::forget($this->loginAttemptKey($request));
        Cache::forget($this->loginLockoutKey($request));
        Cache::forget($this->loginLockoutCountKey($request));
    }
}
