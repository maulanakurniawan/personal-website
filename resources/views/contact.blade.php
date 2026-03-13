<x-layouts.public
    meta-title="Contact - SoloHours"
    meta-description="Contact SoloHours for product questions, billing help, or account support. Send a message and we’ll reply by email."
>
    <section class="mx-auto max-w-4xl space-y-10">
        <div class="max-w-2xl space-y-3">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Contact</p>
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Contact SoloHours</h1>
            <p class="text-base-content/75">
                Have a question about SoloHours, billing, or your account? Send a message and we’ll reply by email.
            </p>
        </div>

        @php($turnstileSiteKey = config('services.turnstile.site_key'))

        <div class="grid gap-6 md:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-2xl bg-base-100">
                <form method="POST" action="{{ route('contact.send') }}" class="space-y-5" data-ga-event="contact">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="form-control">
                            <label for="name" class="label pb-1">
                                <span class="label-text font-medium">Name</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="input input-bordered w-full"
                                required
                                autocomplete="name"
                            >
                        </div>

                        <div class="form-control">
                            <label for="email" class="label pb-1">
                                <span class="label-text font-medium">Email</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="input input-bordered w-full"
                                required
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <div class="form-control">
                        <label for="subject" class="label pb-1">
                            <span class="label-text font-medium">Subject</span>
                        </label>
                        <input
                            id="subject"
                            type="text"
                            name="subject"
                            value="{{ old('subject') }}"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label for="message" class="label pb-1">
                            <span class="label-text font-medium">Message</span>
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="7"
                            class="textarea textarea-bordered w-full"
                            required
                        >{{ old('message') }}</textarea>
                    </div>

                    @if ($turnstileSiteKey)
                        <div class="form-control pt-1">
                            <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                        </div>
                    @else
                        <div class="rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error">
                            Turnstile is currently unavailable. Please try again later.
                        </div>
                    @endif

                    <div class="pt-1">
                        <button type="submit" class="btn btn-primary" @disabled(! $turnstileSiteKey)>
                            Send Message
                        </button>
                    </div>
                </form>
            </section>

            <aside class="space-y-4 md:pt-10">
                <div class="rounded-2xl bg-base-200 p-6">
                    <h2 class="text-lg font-semibold tracking-tight">What to contact us about</h2>
                    <ul class="mt-4 space-y-2 text-sm text-base-content/75">
                        <li>Product questions</li>
                        <li>Billing and subscription help</li>
                        <li>Account access issues</li>
                        <li>General feedback</li>
                    </ul>
                </div>

                <div class="rounded-2xl bg-base-200 p-6">
                    <h2 class="text-lg font-semibold tracking-tight">Support notes</h2>
                    <div class="mt-3 space-y-3 text-sm text-base-content/75">
                        <p>
                            We reply by email and usually keep communication simple and direct.
                        </p>
                        <p>
                            For billing or account issues, include enough detail so we can help faster.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if ($turnstileSiteKey)
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</x-layouts.public>