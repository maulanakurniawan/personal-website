<x-layouts.public
    meta-title="Pricing - SoloHours"
    meta-description="Simple pricing for freelancers, consultants, and solo professionals. Track billable hours, organize projects, and export invoice-ready timesheets."
>
<section class="mx-auto max-w-5xl space-y-14">

    <section class="max-w-2xl space-y-4">
        <p class="text-sm font-medium uppercase tracking-wide text-primary">Pricing</p>

        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">
            Simple pricing for solo work
        </h1>

        <p class="text-base-content/75">
            Choose the plan that fits your workload. Both plans include the core time tracking workflow you need to track work, stay organized, and export clean records for invoicing.
        </p>
    </section>


    <section class="grid gap-6 md:grid-cols-2">

        {{-- STARTER --}}
        <div class="card border border-base-300 bg-base-100">
            <div class="card-body flex h-full flex-col p-6">

                <div class="space-y-2">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ $starterPlan['name'] }}</h2>

                    <p class="text-sm text-base-content/65 line-clamp-2 min-h-[40px]">
                        For freelancers with a smaller active workload.
                    </p>
                </div>

                <div class="mt-6">
                    <div class="flex items-end gap-1">
                        <span class="text-4xl font-bold leading-none">{{ $starterPlan['currency'] === "USD" ? "$" : $starterPlan['currency'] . " " }}{{ rtrim(rtrim(number_format($starterPlan['price_monthly'], 2), "0"), ".") }}</span>
                        <span class="pb-0.5 text-sm text-base-content/65">/ {{ $starterPlan['billing_interval'] }}</span>
                    </div>

                    <p class="mt-2 text-sm text-base-content/70 line-clamp-2 min-h-[40px]">
                        A focused plan for solo professionals who want a simple, reliable time tracker.
                    </p>
                </div>

                <ul class="mt-6 space-y-2 text-[13px] text-base-content/85">
                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>Up to 5 active projects</span>
                    </li>

                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>Unlimited clients</span>
                    </li>

                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>Basic reports</span>
                    </li>

                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>CSV export and uninvoiced time export</span>
                    </li>
                </ul>

                <div class="mt-6 rounded-xl bg-base-200 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Best for
                    </p>

                    <p class="mt-1 text-sm text-base-content/80 line-clamp-2 min-h-[44px]">
                        Freelancers managing a small set of active projects.
                    </p>
                </div>

                <div class="mt-8">
                    @auth
                        <form method="POST" action="{{ route('billing.checkout', ['plan' => $starterPlan['code']]) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm w-full">Choose Starter</button>
                        </form>
                    @else
                        <a href="{{ route('signup', ['plan' => $starterPlan['code']]) }}" class="btn btn-outline btn-sm w-full">Choose Starter</a>
                    @endauth
                </div>

            </div>
        </div>



        {{-- PRO --}}
        <div class="card border border-primary/40 bg-base-100 shadow-sm">
            <div class="card-body flex h-full flex-col p-6">

                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <h2 class="text-2xl font-semibold tracking-tight">{{ $proPlan['name'] }}</h2>

                        <p class="text-sm text-base-content/65 line-clamp-2 min-h-[40px]">
                            For freelancers with more projects and reporting needs.
                        </p>
                    </div>

                    <span class="badge badge-primary badge-sm whitespace-nowrap">
                        Recommended
                    </span>
                </div>

                <div class="mt-6">
                    <div class="flex items-end gap-1">
                        <span class="text-4xl font-bold leading-none">{{ $proPlan['currency'] === "USD" ? "$" : $proPlan['currency'] . " " }}{{ rtrim(rtrim(number_format($proPlan['price_monthly'], 2), "0"), ".") }}</span>
                        <span class="pb-0.5 text-sm text-base-content/65">/ {{ $proPlan['billing_interval'] }}</span>
                    </div>

                    <p class="mt-2 text-sm text-base-content/70 line-clamp-2 min-h-[40px]">
                        Built for solo operators who need more room to grow without changing tools later.
                    </p>
                </div>

                <ul class="mt-6 space-y-2 text-[13px] text-base-content/85">
                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>Unlimited active projects</span>
                    </li>

                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>Unlimited clients</span>
                    </li>

                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>Advanced reports and grouping</span>
                    </li>

                    <li class="flex gap-2">
                        <span class="text-primary leading-none">•</span>
                        <span>CSV and invoice-friendly export</span>
                    </li>
                </ul>

                <div class="mt-6 rounded-xl bg-base-200 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Best for
                    </p>

                    <p class="mt-1 text-sm text-base-content/80 line-clamp-2 min-h-[44px]">
                        Freelancers managing multiple active clients, projects, and more flexible reporting.
                    </p>
                </div>

                <div class="mt-8">
                    @auth
                        <form method="POST" action="{{ route('billing.checkout', ['plan' => $proPlan['code']]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm w-full">Choose Pro</button>
                        </form>
                    @else
                        <a href="{{ route('signup', ['plan' => $proPlan['code']]) }}" class="btn btn-primary btn-sm w-full">Choose Pro</a>
                    @endauth
                </div>

            </div>
        </div>

    </section>



    <section class="rounded-2xl bg-base-200 p-6 md:p-7">
        <div class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold tracking-tight">
                Both plans include the core SoloHours workflow
            </h2>

            <p class="text-base-content/75">
                Every plan includes manual time entry, a running timer, billable and non-billable tracking, invoiced and uninvoiced tracking, and the core workflow for tracking freelance work.
            </p>
        </div>
    </section>


    <section class="space-y-5">

        <div class="max-w-2xl space-y-2">
            <h2 class="text-2xl font-semibold tracking-tight">
                Frequently asked questions
            </h2>
        </div>

        <div class="space-y-3">

            <div class="collapse collapse-arrow rounded-box bg-base-200">
                <input type="radio" name="pricing-faq" checked="checked" />
                <div class="collapse-title font-semibold">
                    Do you limit clients?
                </div>
                <div class="collapse-content text-sm text-base-content/75">
                    No. SoloHours limits active projects on Starter, not clients.
                </div>
            </div>

            <div class="collapse collapse-arrow rounded-box bg-base-200">
                <input type="radio" name="pricing-faq" />
                <div class="collapse-title font-semibold">
                    What happens if I downgrade from Pro to Starter?
                </div>
                <div class="collapse-content text-sm text-base-content/75">
                    Your historical data stays intact. If you have more than 5 projects, the oldest excess projects become locked until you upgrade again.
                </div>
            </div>

            <div class="collapse collapse-arrow rounded-box bg-base-200">
                <input type="radio" name="pricing-faq" />
                <div class="collapse-title font-semibold">
                    Can I mark time as invoiced?
                </div>
                <div class="collapse-content text-sm text-base-content/75">
                    Yes. You can track invoiced and uninvoiced entries and export uninvoiced time.
                </div>
            </div>

            <div class="collapse collapse-arrow rounded-box bg-base-200">
                <input type="radio" name="pricing-faq" />
                <div class="collapse-title font-semibold">
                    Can I track non-billable work?
                </div>
                <div class="collapse-content text-sm text-base-content/75">
                    Yes. You can mark entries as billable or non-billable.
                </div>
            </div>

        </div>

    </section>


    <section class="rounded-2xl bg-base-200 p-6 md:p-8">
        <div class="max-w-2xl space-y-4">
            <h2 class="text-2xl font-semibold tracking-tight">
                Start with the plan that fits your workload
            </h2>

            <p class="text-base-content/75">
                SoloHours keeps pricing simple so you can focus on tracking work.
            </p>

            <div class="flex flex-wrap gap-2">
                @auth
                    <form method="POST" action="{{ route('billing.checkout', ['plan' => $starterPlan['code']]) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Choose Starter</button>
                    </form>
                @else
                    <a href="{{ route('signup', ['plan' => $starterPlan['code']]) }}" class="btn btn-outline btn-sm">Choose Starter</a>
                @endauth

                @auth
                    <form method="POST" action="{{ route('billing.checkout', ['plan' => $proPlan['code']]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Choose Pro</button>
                    </form>
                @else
                    <a href="{{ route('signup', ['plan' => $proPlan['code']]) }}" class="btn btn-primary btn-sm">Choose Pro</a>
                @endauth
            </div>
        </div>
    </section>

</section>
</x-layouts.public>
