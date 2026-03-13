<x-layouts.public
    meta-title="Verify your email - SoloHours"
    meta-description="Verify your SoloHours email address to access your dashboard and start webhook monitoring."
>
    <section class="max-w-lg mx-auto">
        <div class="card">
            <div class="card-body">
                <h1>Verify your email address</h1>
                <p>
                    We sent a verification link to your inbox. Please verify your email before accessing the dashboard.
                </p>

                <div class="mt-6 space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">Resend verification email</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost w-full">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
