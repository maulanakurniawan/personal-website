<x-layouts.public
    meta-title="Sign Up - SoloHours"
    meta-description="Create your SoloHours account and start tracking billable hours across clients and projects."
    meta-robots="noindex, follow"
>
    <section class="mx-auto max-w-5xl">
        @php($turnstileSiteKey = config('services.turnstile.site_key'))

        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
            <section class="rounded-2xl bg-base-200 p-8 md:p-10">
                <div class="max-w-md space-y-5">
                    <p class="text-sm font-medium uppercase tracking-wide text-primary">Sign Up</p>

                    <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">
                        Start tracking time with less friction
                    </h1>

                    <p class="text-base-content/75">
                        Create your SoloHours account to track billable hours, organize work by client and project, and export clean timesheets for invoicing.
                    </p>

                    <div class="space-y-3 pt-2 text-sm text-base-content/80">
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Start a timer or add time manually
                        </div>
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Keep projects and clients organized
                        </div>
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Prepare invoice-friendly exports
                        </div>
                    </div>

                    <div class="rounded-xl border border-base-300 bg-base-100 px-4 py-4">
                        <p class="text-sm font-medium">Built for solo work</p>
                        <p class="mt-1 text-sm text-base-content/70">
                            SoloHours is designed for freelancers, consultants, and independent professionals who want a simple time tracker without bloated team software.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-base-100 p-6 shadow-sm md:p-7 md:mt-16">
                <div class="space-y-2">
                    <h2 class="text-2xl font-semibold tracking-tight">Create your account</h2>
                    <p class="text-sm text-base-content/70">
                        Get started in a few minutes.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('signup.store') }}"
                    class="mt-6 space-y-5"
                    data-ga-event="sign_up"
                >
                    @csrf

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

                    <div class="form-control">
                        <label for="password" class="label pb-1">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="input input-bordered w-full"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="form-control">
                        <label for="password_confirmation" class="label pb-1">
                            <span class="label-text font-medium">Confirm password</span>
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="input input-bordered w-full"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="form-control">
                        @if ($turnstileSiteKey)
                            <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                        @else
                            <div class="rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error">
                                Turnstile is currently unavailable. Please try again later.
                            </div>
                        @endif

                        @error('turnstile')
                            <p class="mt-2 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-sm text-base-content/70">
                        By creating an account, you agree to our
                        <a href="{{ route('terms') }}" class="text-primary hover:underline">Terms</a>
                        and
                        <a href="{{ route('privacy') }}" class="text-primary hover:underline">Privacy Policy</a>.
                    </p>

                    <button type="submit" class="btn btn-primary w-full" @disabled(! $turnstileSiteKey)>
                        Sign Up
                    </button>
                </form>

                <div class="mt-6 border-t border-base-200 pt-5 text-center text-sm text-base-content/70">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a>
                </div>
            </section>
        </div>
    </section>

    @if ($turnstileSiteKey)
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</x-layouts.public>