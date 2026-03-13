<x-layouts.app>
    <div class="max-w-xl mx-auto text-center space-y-4">
        <h1>Opening checkout…</h1>
        <p class="text-base-content/70">
            We&rsquo;re launching the secure Paddle checkout in a moment. If it does not open, please disable any popup
            blockers and try again.
        </p>
        <div class="flex justify-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>
    </div>

    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
    <script>
        Paddle.Environment.set(@json($environment));

        Paddle.Initialize({
            token: @json($clientToken),
            eventCallback: function(data) {
                if (data.name == 'checkout.closed') {
                    window.location.replace('{!! $cancelUrl !!}');
                }
            }
        });

        const checkoutOptions = {
            items: [
                {
                    priceId: @json($priceId),
                    quantity: 1,
                },
            ],
            settings: {
                successUrl: '{!! $successUrl !!}',
            },
            customer: {
                email: @json($customerEmail),
            },
            customData: {
                user_id: @json($userId),
            }
        };

        if (typeof gtag === 'function') {
            gtag('event', 'subscribe', {
                price_id: @json($priceId),
                event_category: 'billing',
            });
        }

        Paddle.Checkout.open(checkoutOptions);
    </script>
</x-layouts.app>
