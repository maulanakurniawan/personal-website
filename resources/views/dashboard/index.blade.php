<x-layouts.app meta-title="Dashboard · SoloHours">
    @php
        $locale = auth()->user()?->locale ?? app()->getLocale();
        $timezone = auth()->user()?->timezone ?? config('app.timezone');
        $formatDuration = static fn (int $seconds): string => sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
    @endphp
    <h1 class="text-lg font-semibold mb-4">Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Tracked Today</p><p class="text-xl font-semibold">{{ $formatDuration($todaySeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Tracked This Week</p><p class="text-xl font-semibold">{{ $formatDuration($weekSeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Tracked This Month</p><p class="text-xl font-semibold">{{ $formatDuration($monthSeconds) }}</p></div></div>
        <div class="card bg-base-200"><div class="card-body p-4"><p class="text-xs">Active Timer</p><p class="text-sm">{{ $activeTimer ? $activeTimer->project->name.' started '.$activeTimer->started_at->diffForHumans() : 'No active timer' }}</p></div></div>
    </div>
    <h2 class="font-semibold mb-2">Recent entries</h2>
    <div class="overflow-x-auto">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Task / Project</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEntries as $entry)
                    <tr>
                        <td>{{ $entry->started_at->clone()->timezone($timezone)->locale($locale)->isoFormat('ll') }}</td>
                        <td>
                            <div>{{ $entry->task ?: '(No task name)' }}</div>
                            <div class="text-xs text-base-content/70">
                                {{ $entry->project->client?->name ?: '-' }} · {{ $entry->project->name ?: '-' }}
                            </div>
                        </td>
                        <td>{{ $formatDuration($entry->duration_seconds) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
