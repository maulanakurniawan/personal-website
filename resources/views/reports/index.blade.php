<x-layouts.app meta-title="Reports · SoloHours">
    @php
        $locale = auth()->user()?->locale ?? app()->getLocale();
        $timezone = auth()->user()?->timezone ?? config('app.timezone');
        $mergeByTask = request()->boolean('merge_by_task') || request()->boolean('merge_by_description');
        $formatDuration = static fn (int $seconds): string => sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        $presetQuery = request()->except(['from', 'to', 'preset']);
    @endphp
    <h1 class="text-lg font-semibold mb-4">Reports</h1>
    <div class="mb-3">
        <div class="mb-1 text-sm font-medium text-base-content/80">Presets</div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index', array_merge($presetQuery, ['preset' => 'today'])) }}" class="btn btn-neutral btn-sm">Today</a>
            <a href="{{ route('reports.index', array_merge($presetQuery, ['preset' => 'this-week'])) }}" class="btn btn-neutral btn-sm">This week</a>
            <a href="{{ route('reports.index', array_merge($presetQuery, ['preset' => 'this-month'])) }}" class="btn btn-neutral btn-sm">This month</a>
            <a href="{{ route('reports.index', array_merge($presetQuery, ['preset' => 'last-month'])) }}" class="btn btn-neutral btn-sm">Last month</a>
        </div>
    </div>

    <div class="mb-1 text-sm font-medium text-base-content/80">Filter</div>
    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="date" name="from" value="{{ request('from') }}" class="input input-bordered" lang="{{ str_replace('_', '-', $locale) }}">
        <input type="date" name="to" value="{{ request('to') }}" class="input input-bordered" lang="{{ str_replace('_', '-', $locale) }}">
        <select name="client_id" class="select select-bordered"><option value="">All clients</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(request('client_id')==$client->id)>{{ $client->name }}</option>@endforeach</select>
        <select name="project_id" class="select select-bordered"><option value="">All projects</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(request('project_id')==$project->id)>{{ $project->name }}</option>@endforeach</select>
        <select name="billable" class="select select-bordered">
            <option value="">All billable statuses</option>
            <option value="1" @selected(request('billable') === '1')>Billable</option>
            <option value="0" @selected(request('billable') === '0')>Unbillable</option>
        </select>
        <select name="invoiced" class="select select-bordered">
            <option value="">All invoice statuses</option>
            <option value="1" @selected(request('invoiced') === '1')>Invoiced</option>
            <option value="0" @selected(request('invoiced') === '0')>Uninvoiced</option>
        </select>
        <label class="label cursor-pointer gap-2 rounded-md px-2 py-1"><input type="checkbox" name="merge_by_task" value="1" class="checkbox" @checked($mergeByTask)><span class="label-text text-sm">Merge by task</span></label>
        <button class="btn btn-primary btn-sm">Apply filters</button>
    </form>

    <div class="grid grid-cols-1 gap-3 mb-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Total hours</p><p class="text-xl font-semibold">{{ $formatDuration($totalSeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Actual billable</p><p class="text-xl font-semibold">{{ $formatDuration($billableSeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Billable (rounded)</p><p class="text-xl font-semibold">{{ $formatDuration($roundedBillableSeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Non-billable</p><p class="text-xl font-semibold">{{ $formatDuration($nonBillableSeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Estimated revenue</p><p class="text-xl font-semibold">{{ $estimatedRevenue !== null ? '$'.number_format($estimatedRevenue, 2) : '—' }}</p></div></div>
    </div>
    <div class="flex gap-2 mb-4"><a href="{{ route('reports.export', request()->query()) }}" class="btn btn-primary btn-sm">Export CSV</a><form method="POST" action="{{ route('reports.mark-invoiced', request()->query()) }}">@csrf<button class="btn btn-secondary btn-sm">Mark filtered as invoiced</button></form></div>
    <table class="table table-sm">
        <thead><tr><th>Start</th><th>End</th><th>{{ $mergeByTask ? 'Task' : 'Task / Project' }}</th><th>Duration</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($entries as $entry)
                @php
                    $status = !($entry->is_billable ?? true) ? 'Non-billable' : (($entry->invoiced_at ?? null) ? 'Invoiced' : 'Uninvoiced');
                    $statusClass = match($status) {
                        'Billable' => 'sh-pill-billable',
                        'Invoiced' => 'sh-pill-invoiced',
                        'Uninvoiced' => 'sh-pill-uninvoiced',
                        default => 'sh-pill-muted',
                    };
                @endphp
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($entry->started_at)->timezone($timezone)->locale($locale)->isoFormat('LLL') }}</td>
                    <td>{{ $entry->ended_at ? \Illuminate\Support\Carbon::parse($entry->ended_at)->timezone($timezone)->locale($locale)->isoFormat('LLL') : '—' }}</td>
                    <td>
                        <div>{{ $entry->task ?: '(No task name)' }}</div>
                        <div class="text-xs text-base-content/70">
                            {{ $mergeByTask ? ($entry->client_name ?: '-') : ($entry->project->client?->name ?: '-') }}
                            ·
                            {{ $mergeByTask ? ($entry->project_name ?: '-') : ($entry->project->name ?: '-') }}
                        </div>
                    </td>
                    <td>{{ $formatDuration((int) $entry->duration_seconds) }}</td>
                    <td>
                        @if($entry->is_billable ?? true)
                            <span class="sh-pill sh-pill-billable">Billable</span>
                        @endif
                        <span class="sh-pill {{ $statusClass }}">{{ $status }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $entries->links() }}
</x-layouts.app>
