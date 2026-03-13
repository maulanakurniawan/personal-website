<x-layouts.public
    meta-title="Sign In - SoloHours"
    meta-description="Sign in to SoloHours to track billable hours, review timesheets, and manage freelance work."
    meta-robots="noindex, follow"
>
    <section class="mx-auto max-w-5xl">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
            <section class="rounded-2xl bg-base-200 p-8 md:p-10">
                <div class="max-w-md space-y-5">
                    <p class="text-sm font-medium uppercase tracking-wide text-primary">Sign In</p>

                    <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">
                        Welcome back to SoloHours
                    </h1>

                    <p class="text-base-content/75">
                        Sign in to review timesheets, track billable hours, and keep your freelance work organized in one simple workspace.
                    </p>

                    <div class="space-y-3 pt-2 text-sm text-base-content/80">
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Track work across clients and projects
                        </div>
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Separate billable and non-billable time
                        </div>
                        <div class="rounded-xl bg-base-100 px-4 py-3">
                            Export clean records for invoicing
                        </div>
                    </div>

                    <p class="text-sm text-base-content/65">
                        SoloHours is built for freelancers, consultants, and solo professionals who want less complexity and clearer records.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl bg-base-100 p-6 shadow-sm md:p-7 md:mt-16">
                <div class="space-y-2">
                    <h2 class="text-2xl font-semibold tracking-tight">Sign in to your account</h2>
                    <p class="text-sm text-base-content/70">
                        Enter your details to continue.
                    </p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5" data-ga-event="login">
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
                            autocomplete="username"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <div class="label pb-1">
                            <label for="password" class="label-text font-medium">Password</label>
                        </div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                            <span class="text-sm text-base-content/75">Remember me</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 border-t border-base-200 pt-5 text-center text-sm text-base-content/70">
                    Don’t have an account yet?
                    <a href="{{ route('signup') }}" class="text-primary hover:underline">Sign up for SoloHours</a>
                </div>
            </section>
        </div>
    </section>
</x-layouts.public>