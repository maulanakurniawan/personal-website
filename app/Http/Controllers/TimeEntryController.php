<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Services\ProjectLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    public function index(Request $request, ProjectLockService $lockService): View
    {
        $user = $request->user();
        $lockService->enforce($user);

        return view('time-entries.index', [
            'entries' => $user->timeEntries()->with(['project.client'])->latest('started_at')->paginate(25),
            'clients' => $user->clients()->orderBy('name')->get(),
            'projects' => $user->projects()->with('client')->whereNull('locked_at')->orderBy('name')->get(),
            'activeTimer' => $user->timeEntries()->whereNull('ended_at')->latest('started_at')->first(),
            'canTrackTime' => $user->canTrackTime(),
        ]);
    }


    public function taskSuggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['tasks' => []]);
        }

        $tasks = $request->user()
            ->timeEntries()
            ->with('project.client')
            ->whereNotNull('task')
            ->where('task', '!=', '')
            ->where('task', 'like', '%'.$query.'%')
            ->latest('started_at')
            ->get()
            ->unique(static fn (TimeEntry $entry): string => mb_strtolower(trim($entry->task ?? '')))
            ->take(8)
            ->values()
            ->map(static fn (TimeEntry $entry): array => [
                'task' => $entry->task ?? '',
                'project_id' => $entry->project_id,
                'project_name' => $entry->project?->name ?? 'Unknown project',
                'client_name' => $entry->project?->client?->name ?? 'No client',
                'is_billable' => (bool) $entry->is_billable,
            ]);

        return response()->json(['tasks' => $tasks]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->canTrackTime()) {
            return back()->with('error', 'Time tracking is available with an active subscription.');
        }

        $data = $request->validate([
            'project_id' => ['required', Rule::exists('projects', 'id')->where('user_id', $request->user()->id)->whereNull('locked_at')],
            'task' => ['nullable', 'string'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after:started_at'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        $entry = $request->user()->timeEntries()->create([
            ...$data,
            'duration_seconds' => \Illuminate\Support\Carbon::parse($data['started_at'])->diffInSeconds(\Illuminate\Support\Carbon::parse($data['ended_at'])),
            'is_billable' => $request->boolean('is_billable', true),
        ]);
        $entry->project()->update(['last_used_at' => $entry->started_at]);

        return back()->with('success', 'Time entry saved.');
    }

    public function start(Request $request): RedirectResponse|JsonResponse
    {
        if (! $request->user()->canTrackTime()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Time tracking is available with an active subscription.'], 403);
            }

            return back()->with('error', 'Time tracking is available with an active subscription.');
        }

        if ($request->user()->timeEntries()->whereNull('ended_at')->exists()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'You already have an active timer.'], 422);
            }

            return back()->with('error', 'You already have an active timer.');
        }

        $data = $request->validate([
            'project_id' => ['required', Rule::exists('projects', 'id')->where('user_id', $request->user()->id)->whereNull('locked_at')],
            'task' => ['nullable', 'string'],
            'initial_seconds' => ['nullable', 'integer', 'min:0', 'max:359999'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        $initialSeconds = (int) ($data['initial_seconds'] ?? 0);

        $entry = $request->user()->timeEntries()->create([
            'project_id' => $data['project_id'],
            'task' => $data['task'] ?? null,
            'started_at' => now()->subSeconds($initialSeconds),
            'is_billable' => $request->boolean('is_billable', true),
        ]);

        $entry->project()->update(['last_used_at' => now()]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'started_at' => $entry->started_at->timestamp,
            ]);
        }

        return back()->with('success', 'Timer started.');
    }

    public function stop(Request $request): RedirectResponse|JsonResponse
    {
        if (! $request->user()->canTrackTime()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Time tracking is available with an active subscription.'], 403);
            }

            return back()->with('error', 'Time tracking is available with an active subscription.');
        }

        $entry = $request->user()->timeEntries()->whereNull('ended_at')->latest('started_at')->first();

        if (! $entry) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'No active timer found.'], 422);
            }

            return back()->with('error', 'No active timer found.');
        }

        $entry->update([
            'ended_at' => now(),
            'duration_seconds' => $entry->started_at->diffInSeconds(now()),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'stopped_at' => $entry->ended_at?->timestamp,
                'duration_seconds' => $entry->duration_seconds,
            ]);
        }

        return back()->with('success', 'Timer stopped.');
    }

    public function continue(Request $request, int $entryId): RedirectResponse
    {
        if (! $request->user()->canTrackTime()) {
            return back()->with('error', 'Time tracking is available with an active subscription.');
        }

        if ($request->user()->timeEntries()->whereNull('ended_at')->exists()) {
            return back()->with('error', 'You already have an active timer.');
        }

        $entry = $request->user()->timeEntries()->with('project')->findOrFail($entryId);

        if ($entry->project->isLocked()) {
            return back()->with('error', 'Timers cannot be started on locked projects.');
        }

        $newEntry = $request->user()->timeEntries()->create([
            'project_id' => $entry->project_id,
            'task' => $entry->task,
            'started_at' => now(),
            'is_billable' => $entry->is_billable,
        ]);

        $newEntry->project()->update(['last_used_at' => now()]);

        return back()->with('success', 'Timer continued from existing entry.');
    }

    public function update(Request $request, int $entryId): RedirectResponse
    {
        $entry = $request->user()->timeEntries()->findOrFail($entryId);

        if ($entry->project->isLocked()) {
            return back()->with('error', 'Entries on locked projects cannot be edited.');
        }

        $data = $request->validate([
            'task' => ['nullable', 'string'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'is_billable' => ['nullable', 'boolean'],
            'invoiced' => ['nullable', 'boolean'],
        ]);

        $startedAt = array_key_exists('started_at', $data) && $data['started_at']
            ? \Illuminate\Support\Carbon::parse($data['started_at'])
            : $entry->started_at;
        $endedAt = array_key_exists('ended_at', $data) && $data['ended_at']
            ? \Illuminate\Support\Carbon::parse($data['ended_at'])
            : null;

        $entry->update([
            'task' => $data['task'] ?? $entry->task,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $endedAt ? $startedAt->diffInSeconds($endedAt) : 0,
            'is_billable' => $request->boolean('is_billable', $entry->is_billable),
            'invoiced_at' => $request->boolean('invoiced') ? ($entry->invoiced_at ?? now()) : null,
        ]);

        return back()->with('success', 'Entry updated.');
    }

    public function destroy(Request $request, int $entryId): RedirectResponse
    {
        $entry = $request->user()->timeEntries()->findOrFail($entryId);

        if ($entry->project->isLocked()) {
            return back()->with('error', 'Entries on locked projects cannot be deleted.');
        }

        $entry->delete();

        return back()->with('success', 'Entry deleted.');
    }
}
