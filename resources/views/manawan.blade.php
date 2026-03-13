<x-layouts.public
    meta-title="Manawan · Personal software initiative by Maulana Kurniawan"
    meta-description="Manawan is a personal initiative by Maulana Kurniawan for building small useful software, focused web tools, and practical product experiments."
>
    <section class="mx-auto max-w-5xl space-y-10">
        <section class="space-y-4">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Manawan</p>
            <h1 class="text-3xl font-bold leading-tight md:text-5xl">A personal initiative for useful small software.</h1>
            <p class="max-w-3xl text-sm text-base-content/75 md:text-base">
                Manawan is where I build practical web products, focused software tools, and technical experiments.
                It is intentionally small and execution-focused.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-2xl border border-base-200 p-5">
                <h2 class="text-xl font-semibold">What Manawan is</h2>
                <p class="mt-2 text-sm text-base-content/75">
                    Manawan is my personal umbrella for product work. It is not a corporation or a studio brand.
                    It is a direct way to ship useful software with clear purpose.
                </p>
            </article>
            <article class="rounded-2xl border border-base-200 p-5">
                <h2 class="text-xl font-semibold">Why it exists</h2>
                <p class="mt-2 text-sm text-base-content/75">
                    Many useful problems are small, specific, and operational. Manawan exists to build solutions for
                    those problems with careful execution instead of hype.
                </p>
            </article>
        </section>

        <section class="space-y-3 rounded-2xl border border-base-200 p-6">
            <h2 class="text-2xl font-semibold">What gets built under Manawan</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Small SaaS products with focused scope.</li>
                <li>Internal tools for repeatable operational workflows.</li>
                <li>Utility web applications for practical day-to-day tasks.</li>
                <li>Technical experiments and product prototypes.</li>
            </ul>
        </section>

        <section class="space-y-3 rounded-2xl border border-base-200 p-6">
            <h2 class="text-2xl font-semibold">How it connects to this website</h2>
            <p class="text-sm text-base-content/75 md:text-base">
                This site is my central profile and writing space as a senior PHP / Laravel developer.
                Manawan is the product track connected to that work.
            </p>
            <p class="text-sm text-base-content/75 md:text-base">
                You can read technical articles here, follow ongoing initiatives, and reach out if you want to discuss
                a project, a tool idea, or a collaboration.
            </p>
        </section>

        <section class="rounded-2xl border border-base-200 p-6">
            <h2 class="text-xl font-semibold">Want to discuss a practical software idea?</h2>
            <p class="mt-2 text-sm text-base-content/75">Send a message and I will get back to you for a focused discussion.</p>
            <a href="{{ route('contact.show') }}" class="btn btn-primary btn-sm mt-4">Contact me</a>
        </section>
    </section>
</x-layouts.public>
