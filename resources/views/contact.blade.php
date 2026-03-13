<x-layouts.public meta-title="Contact · Maulana Kurniawan" meta-description="Contact Maulana Kurniawan for project discussions, consulting, and collaboration.">
    <section class="mx-auto max-w-3xl space-y-6">
        <h1 class="text-4xl font-bold">Contact</h1>
        <p class="text-base text-base-content/75">Use this form to send an inquiry about a project, consulting, collaboration, or technical discussion.</p>

        <form method="POST" action="{{ route('contact.send') }}" class="space-y-4 rounded-2xl border border-base-200 p-6" data-ga-event="contact_form_submit">
            @csrf
            <div>
                <label class="label"><span class="label-text">Name</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full" required>
            </div>
            <div>
                <label class="label"><span class="label-text">Email</span></label>
                <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full" required>
            </div>
            <div>
                <label class="label"><span class="label-text">Subject</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="input input-bordered w-full" required>
            </div>
            <div>
                <label class="label"><span class="label-text">Message</span></label>
                <textarea name="message" rows="6" class="textarea textarea-bordered w-full" required>{{ old('message') }}</textarea>
            </div>
            <div class="space-y-2">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                <p class="text-xs text-base-content/60">Spam protection is enabled with Cloudflare Turnstile.</p>
            </div>
            <button class="btn btn-primary" type="submit">Send message</button>
        </form>

        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    </section>
</x-layouts.public>
