<x-layouts.public
    meta-title="Terms of Use · Maulana Kurniawan"
    meta-description="Terms of use for Maulana Kurniawan personal website, including public pages, articles, and contact form usage."
>
    <section class="mx-auto max-w-4xl space-y-5">
        <h1 class="text-3xl font-bold md:text-4xl">Terms of Use</h1>
        <p class="text-sm text-base-content/70">Effective date: {{ now()->format('F j, Y') }}</p>

        <p class="text-sm text-base-content/75">
            This website shares personal profile content, technical articles, and a contact channel for project or
            collaboration inquiries.
        </p>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Content usage</h2>
            <p class="text-sm text-base-content/75">
                You may read and share public content with attribution. You may not copy in bulk, republish for
                deceptive use, or attempt unauthorized access to website systems.
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Contact submissions</h2>
            <p class="text-sm text-base-content/75">
                If you submit the contact form, provide accurate information and avoid sending unlawful, abusive, or
                malicious content.
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">External services and links</h2>
            <p class="text-sm text-base-content/75">
                This website may use third-party services such as hosting, analytics, and spam protection, and may link
                to external websites. Those services and sites are governed by their own terms.
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">No warranty</h2>
            <p class="text-sm text-base-content/75">
                Content is provided for general informational purposes. No guarantee is made that all information is
                always complete, current, or suitable for a specific use case.
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Contact</h2>
            <p class="text-sm text-base-content/75">
                For legal questions about these terms, use the
                <a href="{{ route('contact.show') }}" class="text-primary hover:underline">contact page</a>.
            </p>
        </section>
    </section>
</x-layouts.public>
