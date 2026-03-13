<x-layouts.public meta-title="Maulana Kurniawan · Senior PHP/Laravel Developer" meta-description="Senior PHP/Laravel developer building practical web applications, internal tools, and small software products.">
    <section class="mx-auto max-w-5xl space-y-12">
        <section class="space-y-5">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Personal website</p>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">I build practical web products with PHP and Laravel.</h1>
            <p class="max-w-3xl text-base text-base-content/75">
                I'm Maulana Kurniawan, a senior PHP/Laravel web developer. I work across backend architecture,
                frontend implementation, deployment, and product execution to ship useful software.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('manawan') }}" class="btn btn-primary">Explore Manawan</a>
                <a href="{{ route('articles.index') }}" class="btn btn-outline">Read Articles</a>
                <a href="{{ route('contact.show') }}" class="btn btn-ghost">Contact</a>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-3">
            <article class="rounded-2xl border border-base-200 bg-base-100 p-5">
                <h2 class="text-lg font-semibold">What I do</h2>
                <p class="mt-2 text-sm text-base-content/75">Build reliable web applications, internal tools, and operational systems with clear business value.</p>
            </article>
            <article class="rounded-2xl border border-base-200 bg-base-100 p-5">
                <h2 class="text-lg font-semibold">Focus areas</h2>
                <p class="mt-2 text-sm text-base-content/75">Laravel architecture, maintainable codebases, integrations, and production-ready delivery.</p>
            </article>
            <article class="rounded-2xl border border-base-200 bg-base-100 p-5">
                <h2 class="text-lg font-semibold">Products</h2>
                <p class="mt-2 text-sm text-base-content/75">Through Manawan, I ship small software products and focused experiments with practical use cases.</p>
            </article>
        </section>
    </section>
</x-layouts.public>
