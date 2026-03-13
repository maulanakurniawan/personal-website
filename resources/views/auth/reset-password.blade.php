<x-layouts.public
    meta-title="Reset Password - SoloHours"
    meta-description="Choose a new password for your SoloHours account."
    meta-robots="noindex, follow"
>
    <section class="mx-auto max-w-5xl">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
            <section class="rounded-2xl bg-base-200 p-8 md:p-10">
                <div class="max-w-md space-y-5">
                    <p class="text-sm font-medium uppercase tracking-wide text-primary">
                        Reset Password
                    </p>

                    <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">
                        Set a new password for your account
                    </h1>

                    <p class="text-base-content/75">
                        Choose a new password to regain access to SoloHours and get back to tracking your freelance work.
                    </p>

                    <div class="space-y-3 pt-2 text-sm text-base-content/80">
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Restore access to your account quickly
                        </div>
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Keep your projects and timesheets secure
                        </div>
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Get back to tracking work without friction
                        </div>
                    </div>

                    <p class="text-sm text-base-content/65">
                        SoloHours is built for freelancers, consultants, and solo professionals who want a simple and reliable workspace.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl bg-base-100 p-6 shadow-sm md:p-7 md:mt-16">
                <div class="space-y-2">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        Choose your new password
                    </h2>
                    <p class="text-sm text-base-content/70">
                        Enter your account details and set a new password below.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="form-control">
                        <label for="email" class="label pb-1">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            autocomplete="email"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label for="password" class="label pb-1">
                            <span class="label-text font-medium">New password</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label for="password_confirmation" class="label pb-1">
                            <span class="label-text font-medium">Confirm new password</span>
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        Reset Password
                    </button>
                </form>

                <div class="mt-6 border-t border-base-200 pt-5 text-center text-sm text-base-content/70">
                    Already reset it?
                    <a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a>
                </div>
            </section>
        </div>
    </section>
</x-layouts.public>