<x-layouts.app>
    <x-slot name="metaTitle">Transactions</x-slot>
    <x-slot name="metaDescription">Review your Paddle transaction and refund events in SoloHours.</x-slot>

    @php
        $locale = auth()->user()?->locale ?? app()->getLocale();
    @endphp

    <div class="space-y-6">
        <div>
            <h1>Transactions</h1>
            <p class="text-base-content/70">Track your payments, refunds, and billing activity in one place.</p>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2>Transactions</h2>
                </div>

                @if ($transactions->isEmpty())
                    <p class="text-sm text-base-content/70">No transactions found yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Refund</th>
                                    <th>Occurred</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="capitalize" data-label="Status">{{ $transaction->status ?? 'unknown' }}</td>
                                        <td data-label="Amount">
                                            @if ($transaction->amount)
                                                {{ $transaction->currency ?? '' }} {{ $transaction->amount }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td data-label="Refund">
                                            @if ($transaction->refund_amount)
                                                {{ $transaction->currency ?? '' }} {{ $transaction->refund_amount }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td data-label="Occurred">
                                            @if ($transaction->occurred_at)
                                                {{ $transaction->occurred_at->locale($locale)->isoFormat('LLL') }}
                                            @else
                                                {{ $transaction->created_at->locale($locale)->isoFormat('LLL') }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-ghost btn-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
