<x-layouts.app>
    <x-slot name="metaTitle">Review subscription change</x-slot>
    <x-slot name="metaDescription">Preview the billing impact before updating your subscription.</x-slot>

    <div class="space-y-6 max-w-2xl">
        <div>
            <h1>Review your change</h1>
            <p class="text-base-content/70">
                You are about to switch from {{ $previousPlan }} to {{ $selectedPlan }}.
            </p>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <h2>Billing preview</h2>
                <p class="text-xs text-base-content/60">All amounts shown are in your billing currency.</p>
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-base-content/60">Charge amount</dt>
                        <dd class="font-medium">
                            @if ($preview['charge_amount'] !== null)
                                {{ $preview['charge_amount'] }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Credit amount</dt>
                        <dd class="font-medium">
                            @if ($preview['credit_amount'] !== null)
                                {{ $preview['credit_amount'] }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Result amount</dt>
                        <dd class="font-medium">
                            @if ($preview['result_amount'] !== null)
                                {{ $preview['result_amount'] }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Amount billed now</dt>
                        <dd class="font-medium">
                            @if ($preview['immediate_total'] !== null)
                                {{ $preview['immediate_total'] }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Next billing date</dt>
                        <dd class="font-medium">{{ $preview['next_billed_at'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Next billing amount</dt>
                        <dd class="font-medium">
                            @if ($preview['next_billed_total'] !== null)
                                {{ $preview['next_billed_total'] }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('billing.history') }}" class="btn btn-ghost">Cancel</a>
            <form
                method="POST"
                action="{{ route('billing.subscription.change') }}"
                data-ga-event="{{ $isUpgrade ? 'upgrade_package' : 'downgrade_package' }}"
                data-ga-plan="{{ $planCode }}"
                data-ga-label="{{ $previousPlan }}_to_{{ $selectedPlan }}"
            >
                @csrf
                <input type="hidden" name="plan" value="{{ $planCode }}">
                <button type="submit" class="btn btn-primary">
                    {{ $isUpgrade ? 'Upgrade now' : 'Downgrade' }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
