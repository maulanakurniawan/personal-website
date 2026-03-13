<x-layouts.app>
    <x-slot name="metaTitle">Subscriptions</x-slot>
    <x-slot name="metaDescription">Review your SoloHours subscriptions and manage your active plan.</x-slot>

    @php
        $locale = auth()->user()?->locale ?? app()->getLocale();
    @endphp

    <div class="space-y-6">
        <div>
            <h1>Subscriptions</h1>
            <p class="text-base-content/70">View your subscriptions and manage your active plan.</p>
        </div>

        <div @class([
            'card',
            'card-highlight' => $activeSubscription,
            'bg-base-200/70 border border-base-300' => ! $activeSubscription,
        ])>
            <div class="card-body space-y-2 p-3 md:p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2>Active subscription</h2>
                        @if ($activeSubscription)
                            <p class="text-sm text-base-content/70 dark:text-emerald-100/80">
                                {{ $planRecords[$activeSubscription->plan]->name ?? ucfirst($activeSubscription->plan) }}
                                <span class="text-base-content/70 dark:text-emerald-200/60">•</span>
                                Renews {{ $activeSubscription->renews_at?->locale($locale)->isoFormat('LL') ?? 'soon' }}
                            </p>
                        @else
                            <p class="text-sm text-base-content/70 dark:text-emerald-100/80">You do not have an active subscription.</p>
                        @endif
                    </div>

                    @if ($activeSubscription)
                        @php
                            $targetPlanCode = $activeSubscription->plan === 'starter' ? 'pro' : 'starter';
                            $targetPlanOption = collect($changePlanOptions)->firstWhere('code', $targetPlanCode);
                            $changePlanCtaLabel = $targetPlanCode === 'pro' ? 'Upgrade to Pro' : 'Downgrade to Starter';
                        @endphp

                        <div class="flex flex-col gap-2">
                            <p class="text-sm text-base-content/70">Change plan</p>
                            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                @if ($targetPlanOption)
                                    <form method="POST" action="{{ route('billing.checkout', ['plan' => $targetPlanOption['code']]) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $targetPlanOption['code'] === 'pro' ? 'btn-primary' : 'btn-outline' }}"
                                            @disabled(! $activeSubscription->paddle_subscription_id)
                                            aria-label="Open Paddle checkout for {{ $targetPlanOption['name'] }}"
                                        >
                                            {{ $changePlanCtaLabel }}
                                        </button>
                                    </form>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('billing.subscription.cancel') }}"
                                    data-ga-event="cancel_subscription"
                                    onsubmit="return confirm('Are you sure you want to cancel your subscription? Access ends immediately. Cancellations in days 1-14 receive a full refund; after that, refunds are prorated for unused days.');">
                                    @csrf
                                    <button type="submit" class="btn btn-error btn-sm">
                                        Cancel subscription
                                    </button>
                                </form>
                            </div>
                            <p class="text-xs text-base-content/60 max-w-xs">
                                Upgrades are prorated immediately. Downgrades take effect at the next billing cycle.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2>Subscriptions</h2>
                </div>

                @if ($subscriptions->isEmpty())
                    <p class="text-sm text-base-content/70">No subscriptions found yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Renews</th>
                                    <th>Ends</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subscriptions as $subscription)
                                    <tr>
                                        <td data-label="Plan">
                                            {{ $planRecords[$subscription->plan]->name ?? ucfirst($subscription->plan ?? 'N/A') }}
                                        </td>
                                        <td class="capitalize" data-label="Status">{{ $subscription->status ?? 'unknown' }}</td>
                                        <td data-label="Renews">
                                            @if ($subscription->renews_at)
                                                {{ $subscription->renews_at->locale($locale)->isoFormat('LLL') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td data-label="Ends">
                                            @if ($subscription->ends_at)
                                                {{ $subscription->ends_at->locale($locale)->isoFormat('LLL') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
