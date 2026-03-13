<x-layouts.public
    meta-title="Maulana Kurniawan · Senior PHP/Laravel Web Developer"
    meta-description="Maulana Kurniawan is a senior PHP/Laravel developer building practical web applications, internal tools, and small software products under Manawan."
>
    <section class="mx-auto max-w-5xl space-y-12">

        <section class="grid gap-8 md:grid-cols-[1fr_auto] md:items-center">
            <div class="space-y-5">
                <p class="text-sm font-medium uppercase tracking-wide text-primary">Maulana Kurniawan</p>

                <h1 class="text-3xl font-bold leading-tight md:text-5xl">
                    Senior PHP / Laravel developer building practical web applications and useful software.
                </h1>

                <p class="max-w-3xl text-sm text-base-content/75 md:text-base">
                    I build and ship web products with clear operational value. My work spans product development,
                    backend systems, frontend implementation, and deployment workflows.
                </p>

                <p class="max-w-3xl text-sm text-base-content/75 md:text-base">
                    I also run
                    <a href="{{ route('manawan') }}" class="text-primary hover:underline">Manawan</a>,
                    a personal initiative for focused software products and technical experiments.
                </p>

                <div class="flex flex-wrap gap-2.5">
                    <a href="{{ route('manawan') }}" class="btn btn-primary btn-sm md:btn-md">View Manawan</a>
                    <a href="{{ route('contact.show') }}" class="btn btn-ghost btn-sm md:btn-md">Contact Me</a>
                </div>
            </div>

            <!-- Avatar -->
            <div class="flex justify-center md:justify-end">
                <img
                    src="/assets/avatar.png"
                    alt="Avatar of Maulana Kurniawan"
                    class="h-36 w-36 rounded-full border border-base-200 md:h-44 md:w-44 lg:h-48 lg:w-48"
                >
            </div>
        </section>


        <section class="space-y-4 rounded-2xl border border-base-200 bg-base-100 p-6">
            <h2 class="text-2xl font-semibold">What I work on</h2>
            <p class="text-sm text-base-content/75 md:text-base">
                I'm a senior web developer focused on Laravel and PHP systems that need to stay maintainable in production.
                Most projects involve feature execution, architecture clean-up, integrations, and delivery from idea to deployment.
            </p>
        </section>

        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Focus areas</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <article class="rounded-2xl border border-base-200 p-5">
                    <h3 class="text-base font-semibold">Laravel & PHP development</h3>
                    <p class="mt-2 text-sm text-base-content/75">Clean backend architecture, APIs, data modeling, and maintainable implementation.</p>
                </article>
                <article class="rounded-2xl border border-base-200 p-5">
                    <h3 class="text-base font-semibold">Product-minded web apps</h3>
                    <p class="mt-2 text-sm text-base-content/75">Shipping practical features that solve real workflow and operational problems.</p>
                </article>
                <article class="rounded-2xl border border-base-200 p-5">
                    <h3 class="text-base font-semibold">Internal tools & systems</h3>
                    <p class="mt-2 text-sm text-base-content/75">Building focused tools for teams that need reliability more than complexity.</p>
                </article>
                <article class="rounded-2xl border border-base-200 p-5">
                    <h3 class="text-base font-semibold">Frontend implementation</h3>
                    <p class="mt-2 text-sm text-base-content/75">Practical UI delivery in Blade/Tailwind stacks with consistent, readable interfaces.</p>
                </article>
                <article class="rounded-2xl border border-base-200 p-5">
                    <h3 class="text-base font-semibold">Deployment workflows</h3>
                    <p class="mt-2 text-sm text-base-content/75">Environment setup, release quality, and production stability across deployments.</p>
                </article>
            </div>
        </section>

        <section class="rounded-2xl border border-base-200 p-6 space-y-3">
            <h2 class="text-2xl font-semibold">Manawan</h2>
            <p class="text-sm text-base-content/75 md:text-base">
                Manawan is where I build small useful software, utility web tools, and focused product ideas.
                The approach is straightforward: solve practical problems, execute carefully, and keep products lean.
            </p>
            <a href="{{ route('manawan') }}" class="btn btn-outline btn-sm">Learn more about Manawan</a>
        </section>

        <section class="rounded-2xl border border-base-200 p-6 space-y-3">
            <h2 class="text-2xl font-semibold">Project or collaboration inquiry</h2>
            <p class="text-sm text-base-content/75 md:text-base">
                If you need help with a web application, internal tool, or product implementation, feel free to reach out.
                I'm open to project discussions, technical consulting, and focused collaboration.
            </p>
            <a href="{{ route('contact.show') }}" class="btn btn-primary btn-sm">Go to contact page</a>
        </section>

    </section>
</x-layouts.public>