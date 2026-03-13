<x-layouts.public
    meta-title="Guides - SoloHours"
    meta-description="Browse practical guides for focused freelance workflows, time tracking habits, invoicing, and client operations."
>
    <section class="mx-auto max-w-5xl space-y-10">
        <header class="rounded-2xl border border-base-200 bg-base-100 p-6 shadow-sm sm:p-8">
            <div class="max-w-3xl space-y-4">
                <p class="text-sm font-medium uppercase tracking-wide text-primary">Guides</p>

                <h1 class="text-3xl font-bold leading-tight sm:text-4xl md:text-5xl">
                    Practical guides for freelancers who want cleaner time tracking
                </h1>

                <p class="text-base leading-7 text-base-content/75 sm:text-lg">
                    Explore focused playbooks for tracking work hours, organizing clients and projects,
                    improving invoicing workflows, and building a smoother freelance operation with SoloHours.
                </p>
            </div>
        </header>

        @if (! empty($articles) && count($articles))
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($articles as $article)
                    <a
                        href="{{ route('article.show', ['slug' => $article['slug']]) }}"
                        class="group flex h-full flex-col justify-between rounded-2xl border border-base-200 bg-base-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md"
                    >
                        <div class="space-y-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-primary/80">
                                Guide
                            </p>

                            <h2 class="text-lg font-semibold leading-7 text-base-content transition group-hover:text-primary">
                                {{ $article['title'] }}
                            </h2>
                        </div>

                        <div class="mt-6 flex items-center text-sm font-medium text-primary">
                            Read guide
                            <span class="ml-1 transition-transform duration-200 group-hover:translate-x-1">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <section class="rounded-2xl border border-dashed border-base-300 bg-base-100 p-8 text-center shadow-sm">
                <div class="mx-auto max-w-2xl space-y-3">
                    <h2 class="text-xl font-semibold">Guides are coming soon</h2>
                    <p class="text-base-content/70">
                        We’re putting together practical articles for freelancers who want better time tracking,
                        cleaner project records, and simpler invoicing workflows.
                    </p>
                </div>
            </section>
        @endif
    </section>
</x-layouts.public>