<x-layouts.app>
    <x-slot name="metaTitle">Transaction details</x-slot>
    <x-slot name="metaDescription">View detailed Paddle transaction and refund webhook data.</x-slot>

    @php
        $locale = auth()->user()?->locale ?? app()->getLocale();
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1>Transaction details</h1>
                <p class="text-base-content/70">{{ $transaction->event_type }} • {{ $transaction->status ?? 'unknown' }}</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost">Back to transactions</a>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <h2>Summary</h2>

                <div class="overflow-x-auto">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Transaction ID</th>
                                <td>{{ $transaction->paddle_transaction_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Adjustment ID</th>
                                <td>{{ $transaction->paddle_adjustment_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Subscription ID</th>
                                <td>{{ $transaction->paddle_subscription_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>{{ $transaction->amount ? (($transaction->currency ?? '') . ' ' . $transaction->amount) : '—' }}</td>
                            </tr>
                            <tr>
                                <th>Refund amount</th>
                                <td>{{ $transaction->refund_amount ? (($transaction->currency ?? '') . ' ' . $transaction->refund_amount) : '—' }}</td>
                            </tr>
                            <tr>
                                <th>Occurred</th>
                                <td>
                                    @if ($transaction->occurred_at)
                                        {{ $transaction->occurred_at->locale($locale)->isoFormat('LLL') }}
                                    @else
                                        {{ $transaction->created_at->locale($locale)->isoFormat('LLL') }}
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-3">
                <h2>Details payload</h2>
                <pre class="bg-base-200 rounded-lg p-4 text-xs overflow-x-auto">{{ json_encode($transaction->details ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-3">
                <h2>Raw webhook payload</h2>
                <pre class="bg-base-200 rounded-lg p-4 text-xs overflow-x-auto">{{ json_encode($transaction->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
</x-layouts.app>
