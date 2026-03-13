<x-layouts.public
    meta-title="Articles · Maulana Kurniawan"
    meta-description="Technical articles by Maulana Kurniawan on Laravel development, web applications, internal tools, and practical software execution."
>
    <section class="mx-auto max-w-4xl space-y-8">
        <header class="space-y-3">
            <h1 class="text-3xl font-bold md:text-4xl">Articles</h1>
            <p class="text-sm text-base-content/75 md:text-base">
                Practical technical writing on PHP/Laravel delivery, web application execution, and building useful software products.
            </p>
        </header>

        <div class="space-y-3">
            @forelse($articles as $article)
                <article class="rounded-2xl border border-base-200 p-5">
                    <h2 class="text-xl font-semibold">
                        <a href="{{ route('article.show', ['slug' => $article['slug']]) }}" class="hover:text-primary">{{ $article['title'] }}</a>
                    </h2>
                    <p class="mt-2 text-sm text-base-content/70">Read the article.</p>
                </article>
            @empty
                <p class="text-sm text-base-content/70">No articles published yet.</p>
            @endforelse
        </div>
    </section>
</x-layouts.public>
