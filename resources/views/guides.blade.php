<x-layouts.public meta-title="Articles · Maulana Kurniawan" meta-description="Articles and practical guides on software delivery, Laravel development, and product execution.">
    <section class="mx-auto max-w-4xl space-y-8">
        <div class="space-y-3">
            <h1 class="text-4xl font-bold">Articles</h1>
            <p class="text-base text-base-content/75">Practical notes on shipping software, Laravel implementation, and focused product work.</p>
        </div>

        <div class="space-y-3">
            @forelse($articles as $article)
                <article class="rounded-2xl border border-base-200 p-5">
                    <h2 class="text-xl font-semibold">
                        <a href="{{ route('article.show', ['slug' => $article['slug']]) }}" class="hover:text-primary">{{ $article['title'] }}</a>
                    </h2>
                    <p class="mt-2 text-sm text-base-content/70">Read the full article.</p>
                </article>
            @empty
                <p class="text-sm text-base-content/70">No articles published yet.</p>
            @endforelse
        </div>
    </section>
</x-layouts.public>
