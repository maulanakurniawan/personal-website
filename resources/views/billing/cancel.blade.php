<x-layouts.public
    meta-title="Checkout Canceled - SoloHours"
    meta-description="Your SoloHours checkout was canceled. You can restart pricing and choose a plan anytime."
>
    <section class="max-w-2xl mx-auto text-center space-y-4">
        <h1>Checkout canceled</h1>
        <p class="text-base-content/70">
            Your subscription was not completed. You can start checkout again whenever you are ready.
        </p>
        <a href="{{ route('pricing') }}" class="btn btn-outline">Back to pricing</a>
    </section>
</x-layouts.public>
