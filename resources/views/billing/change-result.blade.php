<x-layouts.app>
    <x-slot name="metaTitle">Subscription updated</x-slot>
    <x-slot name="metaDescription">Review the result of your subscription change.</x-slot>

    <div class="space-y-6 max-w-2xl">
        <div>
            <h1>Subscription updated</h1>
            <p class="text-base-content/70">
                Your subscription change from {{ $previousPlan }} to {{ $selectedPlan }} has been submitted.
            </p>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <h2>Details</h2>
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-base-content/60">Subscription ID</dt>
                        <dd class="font-medium">{{ $details['id'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Status</dt>
                        <dd class="font-medium capitalize">{{ $details['status'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Next billed at</dt>
                        <dd class="font-medium">{{ $details['next_billed_at'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Current period ends</dt>
                        <dd class="font-medium">{{ $details['current_period_ends_at'] ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
        </div>
    </div>
</x-layouts.app>
