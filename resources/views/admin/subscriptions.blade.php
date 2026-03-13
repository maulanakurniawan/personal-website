<x-layouts.app :admin-backend="true">
    <x-slot name="metaTitle">Admin · Subscriptions</x-slot>

    @php
        $locale = app()->getLocale();
    @endphp

    <div class="space-y-6">
        <div>
            <h1>Admin · Subscriptions</h1>
            <p class="text-base-content/70">Review all user subscriptions.</p>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                @if ($subscriptions->isEmpty())
                    <p class="text-sm text-base-content/70">No subscriptions found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Renews</th>
                                    <th>Ends</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subscriptions as $subscription)
                                    <tr>
                                        <td>{{ $subscription->user?->name ?? '—' }}</td>
                                        <td>{{ $subscription->user?->email ?? '—' }}</td>
                                        <td>{{ ucfirst($subscription->plan ?? 'n/a') }}</td>
                                        <td>{{ ucfirst($subscription->status ?? 'unknown') }}</td>
                                        <td>{{ $subscription->renews_at?->locale($locale)->isoFormat('LLL') ?? '—' }}</td>
                                        <td>{{ $subscription->ends_at?->locale($locale)->isoFormat('LLL') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $subscriptions->links() }}
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
