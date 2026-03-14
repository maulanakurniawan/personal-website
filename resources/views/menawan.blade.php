<x-layouts.public
    meta-title="Menawan - Personal software initiative by Maulana Kurniawan"
    meta-description="Menawan is a personal software initiative by Maulana Kurniawan for building small useful software, focused SaaS products, and practical web tools."
>
    <section class="mx-auto max-w-5xl space-y-10">
        <section class="flex items-start gap-3 md:gap-4">
            <img
                src="/assets/menawan-logo.png"
                alt="Menawan logo"
                class="h-14 w-14 mt-6 shrink-0 rounded-2xl object-contain md:h-[7.5rem] md:w-[7.5rem]"
            >

            <div class="space-y-4">
                <p class="text-sm font-medium uppercase tracking-wide text-primary">Menawan</p>

                <h1 class="text-3xl font-bold leading-tight md:text-5xl">
                    A personal initiative for useful small software.
                </h1>

                <p class="max-w-3xl text-sm text-base-content/75 md:text-base">
                    Menawan is where I build practical web products, focused software tools, and technical experiments.
                    It is intentionally small and execution-focused.
                </p>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-2xl border border-base-200 p-5">
                <h2 class="text-xl font-semibold">What Menawan is</h2>
                <p class="mt-2 text-sm text-base-content/75">
                    Menawan is my personal umbrella for product work. It is not a corporation or a studio brand.
                    It is a direct way to ship useful software with clear purpose.
                </p>
            </article>

            <article class="rounded-2xl border border-base-200 p-5">
                <h2 class="text-xl font-semibold">Why it exists</h2>
                <p class="mt-2 text-sm text-base-content/75">
                    Many useful problems are small, specific, and operational. Menawan exists to build solutions for
                    those problems with careful execution instead of hype.
                </p>
            </article>
        </section>

        <section class="space-y-3 rounded-2xl border border-base-200 p-6">
            <h2 class="text-2xl font-semibold">What gets built under Menawan</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Small SaaS products with focused scope.</li>
                <li>Internal tools for repeatable operational workflows.</li>
                <li>Utility web applications for practical day-to-day tasks.</li>
                <li>Technical experiments and product prototypes.</li>
            </ul>
        </section>

        <section class="space-y-5 rounded-2xl border border-base-200 p-6">
            <div class="space-y-2">
                <h2 class="text-2xl font-semibold">Products under Menawan</h2>
                <p class="text-sm text-base-content/75 md:text-base">
                    Menawan currently includes focused SaaS products built around practical operational use cases.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <article class="rounded-2xl border border-base-200 bg-base-100 p-5">
                    <div class="flex items-start gap-4">
                        <img
                            src="/assets/webhookwatch-logo.png"
                            alt="WebhookWatch logo"
                            class="h-12 w-12 rounded-xl border border-base-200 object-contain"
                        >

                        <div class="space-y-2">
                            <h3 class="text-lg font-semibold">WebhookWatch</h3>
                            <p class="text-sm text-base-content/75">
                                A focused webhook monitoring service for tracking delivery issues, endpoint failures,
                                and silent webhook problems in production.
                            </p>
                            <a
                                href="https://www.webhookwatch.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm font-medium text-primary hover:underline"
                            >
                                Visit WebhookWatch →
                            </a>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-base-200 bg-base-100 p-5">
                    <div class="flex items-start gap-4">
                        <img
                            src="/assets/solohours-logo.png"
                            alt="SoloHours logo"
                            class="h-12 w-12 rounded-xl border border-base-200 object-contain"
                        >

                        <div class="space-y-2">
                            <h3 class="text-lg font-semibold">SoloHours</h3>
                            <p class="text-sm text-base-content/75">
                                A lightweight time tracking tool for freelancers who want simple project-based tracking,
                                cleaner reports, and practical workflow control.
                            </p>
                            <a
                                href="https://www.solohours.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm font-medium text-primary hover:underline"
                            >
                                Visit SoloHours →
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="space-y-3 rounded-2xl border border-base-200 p-6">
            <h2 class="text-2xl font-semibold">How it connects to this website</h2>
            <p class="text-sm text-base-content/75 md:text-base">
                This site is my central profile and writing space as a senior PHP / Laravel developer.
                Menawan is the product track connected to that work.
            </p>
            <p class="text-sm text-base-content/75 md:text-base">
                You can read technical articles here, follow ongoing initiatives, and explore products like
                WebhookWatch and SoloHours that are built under Menawan.
            </p>
        </section>

        <section class="rounded-2xl border border-base-200 p-6">
            <h2 class="text-xl font-semibold">Want to discuss a practical software idea?</h2>
            <p class="mt-2 text-sm text-base-content/75">
                Send a message and I will get back to you for a focused discussion.
            </p>
            <a href="{{ route('contact.show') }}" class="btn btn-primary btn-sm mt-4">Contact me</a>
        </section>
    </section>
</x-layouts.public>