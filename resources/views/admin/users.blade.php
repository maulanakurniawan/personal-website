<x-layouts.app :admin-backend="true">
    <x-slot name="metaTitle">Admin · Users</x-slot>

    <div class="space-y-6">
        <div>
            <h1>Admin · Users</h1>
            <p class="text-base-content/70">Review all users in SoloHours.</p>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                @if ($users->isEmpty())
                    <p class="text-sm text-base-content/70">No users found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Admin</th>
                                    <th>Subscriptions</th>
                                    <th>Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->planRecord?->name ?? ucfirst($user->plan) }}</td>
                                        <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                                        <td>{{ $user->subscriptions_count }}</td>
                                        <td>{{ $user->paddle_transactions_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $users->links() }}
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
