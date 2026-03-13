<x-layouts.public
    meta-title="Reset Password - SoloHours"
    meta-description="Request a password reset link for your SoloHours account."
    meta-robots="noindex, follow"
>
<section class="mx-auto max-w-5xl">

    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">

        {{-- Left panel --}}
        <section class="rounded-2xl bg-base-200 p-8 md:p-10">

            <div class="max-w-md space-y-5">

                <p class="text-sm font-medium uppercase tracking-wide text-primary">
                    Password Reset
                </p>

                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">
                    Reset your SoloHours password
                </h1>

                <p class="text-base-content/75">
                    Enter the email address associated with your account and we’ll send you a secure password reset link.
                </p>

                <div class="space-y-3 pt-2 text-sm text-base-content/80">

                    <div class="rounded-xl bg-base-100 px-4 py-3">
                        Reset your password in seconds
                    </div>

                    <div class="rounded-xl bg-base-100 px-4 py-3">
                        Access your time tracking again quickly
                    </div>

                    <div class="rounded-xl bg-base-100 px-4 py-3">
                        Keep your account secure
                    </div>

                </div>

                <p class="text-sm text-base-content/65">
                    SoloHours keeps your time tracking data safe and accessible when you need it.
                </p>

            </div>

        </section>


        {{-- Form --}}
        <section class="rounded-2xl bg-base-100 p-6 shadow-sm md:p-7 md:mt-16">

            <div class="space-y-2">

                <h2 class="text-2xl font-semibold tracking-tight">
                    Request password reset
                </h2>

                <p class="text-sm text-base-content/70">
                    We'll email you a link to reset your password.
                </p>

            </div>

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">

                @csrf

                <div class="form-control">
                    <label for="email" class="label pb-1">
                        <span class="label-text font-medium">Email</span>
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        class="input input-bordered w-full"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Email Reset Link
                </button>

            </form>

            <div class="mt-6 border-t border-base-200 pt-5 text-center text-sm text-base-content/70">

                Remembered your password?

                <a href="{{ route('login') }}" class="text-primary hover:underline">
                    Sign in
                </a>

            </div>

        </section>

    </div>

</section>
</x-layouts.public>