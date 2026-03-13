<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'cf-turnstile-response' => ['required', 'string'],
        ]);

        $secretKey = config('services.turnstile.secret_key');

        if (empty($secretKey)) {
            return back()
                ->withErrors(['turnstile' => 'Turnstile is not configured. Please try again later.'])
                ->withInput();
        }

        $verification = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $validated['cf-turnstile-response'],
            'remoteip' => $request->ip(),
        ]);

        if (! $verification->successful() || ! data_get($verification->json(), 'success')) {
            return back()
                ->withErrors(['turnstile' => 'Please complete Turnstile verification and try again.'])
                ->withInput();
        }

        Mail::to(config('mail.support.address', 'hello@maulanakurniawan.com'))->send(new ContactFormMail(
            $validated['name'],
            $validated['email'],
            $validated['subject'],
            $validated['message'],
        ));

        return back()->with('success', 'Thanks, your message has been sent.');
    }
}
