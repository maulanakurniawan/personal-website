<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen">
    <header class="bg-slate-950 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between p-4">
            <div>
                <strong>Admin Hub</strong>
                <span class="ml-3 text-slate-300">{{ $product['name'] ?? '' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <select class="rounded bg-slate-800 p-2" onchange="location.href='/admin/'+this.value+'/resources'">
                    @foreach(config('admin-hub.products') as $key => $item)
                        <option value="{{ $key }}" @selected($key === $productKey)>{{ $item['name'] }}</option>
                    @endforeach
                </select>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="rounded bg-slate-800 px-3 py-2">Logout</button>
                </form>
            </div>
        </div>
    </header>
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 p-4 md:grid-cols-[220px_1fr]">
        <nav class="rounded bg-white p-4 shadow">
            @php($resourceNavigation = $resourceNavigation ?? [])
            @if($resourceNavigation)
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Resources</p>
                <ul class="space-y-2">
                    @foreach($resourceNavigation as $navKey => $resource)
                        @php($navLabel = $resource['label'] ?? \Illuminate\Support\Str::headline($navKey))
                        <li>
                            <a class="block rounded px-3 py-2 {{ ($resourceKey ?? null) === $navKey ? 'bg-blue-50 text-blue-700' : 'hover:bg-slate-50' }}" href="{{ route('admin.product.resources.table', [$productKey, $navKey]) }}">
                                {{ $navLabel }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="rounded bg-slate-50 p-3 text-sm text-slate-500">No resources available.</div>
            @endif
        </nav>
        <main>
            @if(session('status'))<div class="mb-4 rounded bg-green-50 p-3 text-green-700">{{ session('status') }}</div>@endif
            @if(session('error'))<div class="mb-4 rounded bg-red-50 p-3 text-red-700">{{ session('error') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
