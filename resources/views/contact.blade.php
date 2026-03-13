<x-layouts.public
    meta-title="Contact · Maulana Kurniawan"
    meta-description="Contact Maulana Kurniawan for project discussions, technical consulting, and collaboration on web applications and software tools."
>
    <section class="mx-auto max-w-3xl space-y-6">
        <header class="space-y-3">
            <h1 class="text-3xl font-bold md:text-4xl">Contact</h1>
            <p class="text-sm text-base-content/75 md:text-base">
                If you want to discuss a project, collaboration, or technical consulting work, send a message here.
            </p>
            <p class="text-xs text-base-content/60 md:text-sm">
                Helpful context: project scope, timeline, current stack, and what kind of support you need.
            </p>
        </header>

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
                <input type="text" name="subject" value="{{ old('subject') }}" class="input input-bordered w-full" placeholder="Project inquiry, collaboration, consulting, etc." required>
            </div>
            <div>
                <label class="label"><span class="label-text">Message</span></label>
                <textarea name="message" rows="6" class="textarea textarea-bordered w-full" placeholder="Share a short overview so I can respond with the right context." required>{{ old('message') }}</textarea>
            </div>
            <div class="space-y-2">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                <p class="text-xs text-base-content/60">Spam protection is enabled with Cloudflare Turnstile.</p>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">Send message</button>
        </form>

        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    </section>
</x-layouts.public>
