<x-layouts.public
    meta-title="Subscription Activated - SoloHours"
    meta-description="Your SoloHours subscription is active. Start tracking your projects and time from your dashboard."
>
    <section class="max-w-2xl mx-auto text-center space-y-4">
        <h1>Thanks for subscribing!</h1>
        <p class="text-base-content/70">
            Subscription activated shortly. Status will update automatically.
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
    </section>

    <script>
        (() => {
            if (typeof gtag !== 'function') {
                return;
            }

            const params = new URLSearchParams(window.location.search);
            const transactionId = params.get('transaction_id')
                || params.get('txn_id')
                || params.get('checkout_id')
                || `solo-hours-${Date.now()}`;

            gtag('event', 'purchase', {
                transaction_id: transactionId,
                currency: 'USD',
                event_category: 'billing',
            });
        })();
    </script>
</x-layouts.public>
