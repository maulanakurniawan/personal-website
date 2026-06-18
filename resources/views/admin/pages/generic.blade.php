@extends('admin.layout')

@section('content')
<section class="rounded bg-white p-6 shadow">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <h1 class="text-2xl font-bold">{{ \Illuminate\Support\Str::headline($section) }}</h1>

        @if($section === 'user-detail')
            <form method="POST" action="{{ route('admin.product.users.destroy', [$productKey, request()->route('id')]) }}">
                @csrf
                @method('DELETE')
                <button
                    class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                    onclick="return confirm('Permanently delete this user and all related data? This cannot be undone.')"
                >Delete user</button>
            </form>
        @endif
    </div>

    @if(! $response->success)
        <div class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-red-800">
            {{ $response->error['message'] ?? 'Unable to load data.' }}
        </div>
    @else
        @php($items = $data['items'] ?? null)

        @if(is_array($items))
            <div class="mt-6 overflow-auto rounded border">
                <table class="min-w-full divide-y">
                    <tbody class="divide-y">
                        @forelse($items as $item)
                            <tr>
                                @foreach((array) $item as $k => $v)
                                    <td class="p-3">
                                        <strong>{{ \Illuminate\Support\Str::headline($k) }}</strong><br>
                                        @include('admin.components.field', ['value' => $v, 'field' => ['type' => is_bool($v) ? 'boolean' : (is_array($v) ? 'json' : 'text')], 'table' => true])
                                    </td>
                                @endforeach

                                @if($section === 'users')
                                    @php($userId = $item['id'] ?? $item['uuid'] ?? null)
                                    <td class="p-3 text-right">
                                        @if($userId)
                                            <a class="mr-3 text-blue-700 hover:underline" href="{{ route('admin.product.users.show', [$productKey, $userId]) }}">View</a>
                                            <form method="POST" action="{{ route('admin.product.users.destroy', [$productKey, $userId]) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-700 hover:underline" onclick="return confirm('Permanently delete this user and all related data? This cannot be undone.')">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td class="p-8 text-center text-slate-500">No items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach($data as $key => $value)
                    <div class="rounded border p-4">
                        <div class="text-sm text-slate-500">{{ \Illuminate\Support\Str::headline($key) }}</div>
                        <div class="mt-2 text-lg font-semibold">
                            @include('admin.components.field', ['value' => $value, 'field' => ['type' => is_bool($value) ? 'boolean' : (is_array($value) ? 'json' : 'text')]])
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    @if($section === 'settings')
        <form method="POST" action="{{ route('admin.product.settings.update', $productKey) }}" class="mt-6">
            @csrf
            @method('PATCH')
            <button class="rounded bg-blue-600 px-4 py-2 text-white" onclick="return confirm('Submit safe settings update?')">Submit settings update</button>
        </form>
    @endif
</section>
@endsection
