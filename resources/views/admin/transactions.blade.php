<x-layouts.app :admin-backend="true">
    <x-slot name="metaTitle">Admin · Transactions</x-slot>

    @php
        $locale = app()->getLocale();
    @endphp

    <div class="space-y-6">
        <div>
            <h1>Admin · Transactions</h1>
            <p class="text-base-content/70">Review all Paddle transaction events.</p>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                @if ($transactions->isEmpty())
                    <p class="text-sm text-base-content/70">No transactions found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Refund</th>
                                    <th>Occurred</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->user?->name ?? '—' }}</td>
                                        <td>{{ $transaction->user?->email ?? '—' }}</td>
                                        <td>{{ ucfirst($transaction->status ?? 'unknown') }}</td>
                                        <td>{{ $transaction->amount ? (($transaction->currency ?? '') . ' ' . $transaction->amount) : '—' }}</td>
                                        <td>{{ $transaction->refund_amount ? (($transaction->currency ?? '') . ' ' . $transaction->refund_amount) : '—' }}</td>
                                        <td>{{ $transaction->occurred_at?->locale($locale)->isoFormat('LLL') ?? $transaction->created_at->locale($locale)->isoFormat('LLL') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $transactions->links() }}
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
