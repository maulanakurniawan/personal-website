<?php

namespace App\Http\Controllers;

use App\Mail\AccountDeletedMail;
use App\Services\PaddleSubscriptionRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function confirmDelete(Request $request): View
    {
        return view('profile.delete', [
            'user' => $request->user(),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:DELETE'],
        ], [
            'confirmation.in' => 'Please type DELETE to confirm.',
        ]);

        $user = $request->user();
        $subscription = $user->activeSubscription()->first();

        if ($subscription) {
            $apiKey = config('paddle.api_key');

            if (! $apiKey) {
                return back()->with('error', 'Billing is not configured yet. Please contact support.');
            }

            if (! $subscription->paddle_subscription_id) {
                return back()->with('error', 'Subscription details are missing. Please contact support.');
            }

            $paddleEnvironment = config('paddle.environment') === 'production'
                ? \Paddle\SDK\Environment::PRODUCTION
                : \Paddle\SDK\Environment::SANDBOX;

            $paddle = new \Paddle\SDK\Client($apiKey, options: new \Paddle\SDK\Options($paddleEnvironment));

            try {
                $response = $paddle->subscriptions->cancel(
                    $subscription->paddle_subscription_id,
                    new \Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription(
                        \Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom::Immediately()
                    )
                );
            } catch (\Throwable $exception) {
                return back()->with('error', 'We could not cancel your subscription. Please contact support.');
            }

            $refundService = new PaddleSubscriptionRefundService();

            try {
                $refundService->issueRefund($paddle, $subscription);
            } catch (\Throwable $exception) {
                report($exception);
            }

            $status = data_get($response, 'status')
                ?? data_get($response, 'data.status');
            $endsAt = data_get($response, 'current_billing_period.ends_at')
                ?? data_get($response, 'currentBillingPeriod.endsAt')
                ?? data_get($response, 'data.current_billing_period.ends_at')
                ?? now();
            $renewsAt = $endsAt;

            $subscription->update([
                'status' => $status ?? $subscription->status,
                'ends_at' => $endsAt,
                'renews_at' => $renewsAt,
            ]);
        }

        Mail::to($user->email)->send(new AccountDeletedMail($user->name));

        DB::transaction(function () use ($user): void {
            $user->subscriptions()->delete();
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }
}
