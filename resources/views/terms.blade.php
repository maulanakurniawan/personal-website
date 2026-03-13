<x-layouts.public meta-title="Terms of Use · Maulana Kurniawan" meta-description="Terms of use for Maulana Kurniawan personal website.">
    <section class="mx-auto max-w-4xl space-y-5">
        <h1 class="text-4xl font-bold">Terms of Use</h1>
        <p class="text-sm text-base-content/70">Effective date: {{ now()->format('F j, Y') }}</p>
        <p class="text-sm text-base-content/75">This website provides personal profile information, technical articles, and a contact channel.</p>
        <h2 class="text-xl font-semibold">Use of content</h2>
        <p class="text-sm text-base-content/75">You may read and share public content with attribution. Do not misuse, copy in bulk, or attempt unauthorized access to website systems.</p>
        <h2 class="text-xl font-semibold">No guarantees</h2>
        <p class="text-sm text-base-content/75">Content is provided for general informational purposes. While accuracy is a goal, no warranty is provided for completeness or fitness for a specific purpose.</p>
        <h2 class="text-xl font-semibold">Contact</h2>
        <p class="text-sm text-base-content/75">For legal questions, use the <a href="{{ route('contact.show') }}" class="text-primary hover:underline">contact page</a>.</p>
    </section>
</x-layouts.public>
