<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->applyPresetDateRange($request);

        $mergeByTask = $request->boolean('merge_by_task') || $request->boolean('merge_by_description');

        $entries = $mergeByTask
            ? $this->mergedEntries($request)
            : $this->query($request)->with(['project.client'])->paginate(25)->withQueryString();
        $totals = (clone $this->query($request))->with('project')->get();

        $actualSeconds = (int) $totals->sum('duration_seconds');
        $billableSeconds = (int) $totals->where('is_billable', true)->sum('duration_seconds');
        $nonBillableSeconds = (int) $totals->where('is_billable', false)->sum('duration_seconds');
        $roundedBillableSeconds = $this->calculateRoundedBillableSeconds($totals);
        $estimatedRevenue = $this->calculateRevenue($totals);

        return view('reports.index', [
            'entries' => $entries,
            'totalSeconds' => $actualSeconds,
            'billableSeconds' => $billableSeconds,
            'nonBillableSeconds' => $nonBillableSeconds,
            'roundedBillableSeconds' => $roundedBillableSeconds,
            'estimatedRevenue' => $estimatedRevenue,
            'clients' => $request->user()->clients()->orderBy('name')->get(),
            'projects' => $request->user()->projects()->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->applyPresetDateRange($request);
        $mergeByTask = $request->boolean('merge_by_task') || $request->boolean('merge_by_description');

        $entries = $mergeByTask
            ? $this->mergedEntriesQuery($request)->get()
            : $this->query($request)->with(['project.client'])->get();

        return response()->streamDownload(function () use ($entries, $mergeByTask) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Client', 'Project', 'Task', 'Duration Hours', 'Billable', 'Invoice Status']);
            foreach ($entries as $entry) {
                fputcsv($out, [
                    $entry->started_at->toDateString(),
                    $this->exportClientName($entry, $mergeByTask),
                    $this->exportProjectName($entry, $mergeByTask),
                    $entry->task,
                    round($entry->duration_seconds / 3600, 2),
                    $mergeByTask ? $this->mergedBillableStatus($entry) : ($entry->is_billable ? 'Yes' : 'No'),
                    $mergeByTask ? $this->mergedInvoiceStatus($entry) : $this->invoiceStatus($entry),
                ]);
            }
            fclose($out);
        }, 'solo-hours-export.csv');
    }


    private function exportClientName(object $entry, bool $mergeByTask): string
    {
        $clientName = $mergeByTask
            ? ($entry->client_name ?? null)
            : ($entry->project->client?->name ?? null);

        return $clientName ?: '-';
    }

    private function exportProjectName(object $entry, bool $mergeByTask): string
    {
        $projectName = $mergeByTask
            ? ($entry->project_name ?? null)
            : ($entry->project->name ?? null);

        return $projectName ?: '-';
    }

    public function markInvoiced(Request $request)
    {
        $this->applyPresetDateRange($request);

        $ids = $this->query($request)->pluck('id');
        TimeEntry::whereIn('id', $ids)->where('is_billable', true)->update(['invoiced_at' => now()]);

        return back()->with('success', 'Filtered billable entries marked as invoiced.');
    }

    private function query(Request $request): Builder|HasMany
    {
        $invoiced = $request->input('invoiced');
        $billable = $request->input('billable');

        return $request->user()->timeEntries()
            ->when($request->filled('from'), fn (Builder $q) => $q->whereDate('started_at', '>=', $request->string('from')))
            ->when($request->filled('to'), fn (Builder $q) => $q->whereDate('started_at', '<=', $request->string('to')))
            ->when($request->filled('client_id'), fn (Builder $q) => $q->whereHas('project', fn (Builder $pq) => $pq->where('client_id', $request->integer('client_id'))))
            ->when($request->filled('project_id'), fn (Builder $q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('invoiced'), function (Builder $q) use ($invoiced) {
                if ((string) $invoiced === '1') {
                    $q->whereNotNull('invoiced_at');
                }

                if ((string) $invoiced === '0') {
                    $q->whereNull('invoiced_at');
                }
            })
            ->when($request->filled('billable'), function (Builder $q) use ($billable) {
                if ((string) $billable === '1') {
                    $q->where('is_billable', true);
                }

                if ((string) $billable === '0') {
                    $q->where('is_billable', false);
                }
            })
            ->orderByDesc('started_at');
    }

    private function mergedEntries(Request $request): LengthAwarePaginator
    {
        return $this->mergedEntriesQuery($request)
            ->orderByDesc('started_at')
            ->paginate(25)
            ->withQueryString();
    }

    private function mergedEntriesQuery(Request $request): Builder|HasMany
    {
        return $this->query($request)
            ->join('projects', 'projects.id', '=', 'time_entries.project_id')
            ->leftJoin('clients', 'clients.id', '=', 'projects.client_id')
            ->selectRaw('MIN(time_entries.id) as id, TRIM(COALESCE(time_entries.task, "")) as task, MIN(time_entries.started_at) as started_at, MAX(time_entries.ended_at) as ended_at, SUM(time_entries.duration_seconds) as duration_seconds, MIN(CASE WHEN time_entries.is_billable THEN 1 ELSE 0 END) as min_billable, MAX(CASE WHEN time_entries.is_billable THEN 1 ELSE 0 END) as max_billable, MIN(CASE WHEN time_entries.invoiced_at IS NULL THEN 0 ELSE 1 END) as min_invoiced, MAX(CASE WHEN time_entries.invoiced_at IS NULL THEN 0 ELSE 1 END) as max_invoiced, projects.name as project_name, clients.name as client_name')
            ->groupByRaw('TRIM(COALESCE(time_entries.task, "")), projects.id, projects.name, clients.name');
    }

    private function resolvePresetDates(string $preset): array
    {
        $today = now();

        return match ($preset) {
            'today' => [$today->toDateString(), $today->toDateString()],
            'this-week' => [$today->copy()->startOfWeek()->toDateString(), $today->copy()->endOfWeek()->toDateString()],
            'this-month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last-month' => [$today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            default => [null, null],
        };
    }

    private function applyPresetDateRange(Request $request): void
    {
        [$from, $to] = $this->resolvePresetDates($request->string('preset')->toString());
        if (! $from || ! $to) {
            return;
        }

        $request->merge([
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function calculateRoundedBillableSeconds(Collection $entries): int
    {
        return (int) $entries->where('is_billable', true)->sum(function (TimeEntry $entry) {
            $project = $entry->project;

            if (! $project || ! $project->rounding_enabled || ! $project->rounding_unit_minutes) {
                return $entry->duration_seconds;
            }

            $unitSeconds = $project->rounding_unit_minutes * 60;

            return (int) (ceil($entry->duration_seconds / $unitSeconds) * $unitSeconds);
        });
    }

    private function calculateRevenue(Collection $entries): ?float
    {
        $revenue = 0.0;
        $hasRate = false;

        foreach ($entries->where('is_billable', true) as $entry) {
            $project = $entry->project;

            if (! $project || $project->hourly_rate === null) {
                continue;
            }

            $hasRate = true;
            $seconds = $entry->duration_seconds;
            if ($project->rounding_enabled && $project->rounding_unit_minutes) {
                $unitSeconds = $project->rounding_unit_minutes * 60;
                $seconds = (int) (ceil($seconds / $unitSeconds) * $unitSeconds);
            }

            $revenue += ((float) $project->hourly_rate) * ($seconds / 3600);
        }

        return $hasRate ? round($revenue, 2) : null;
    }

    public function invoiceStatus(TimeEntry $entry): string
    {
        if (! $entry->is_billable) {
            return 'Non-billable';
        }

        return $entry->invoiced_at ? 'Invoiced' : 'Uninvoiced';
    }

    private function mergedBillableStatus(object $entry): string
    {
        if ((int) $entry->min_billable === 1 && (int) $entry->max_billable === 1) {
            return 'Yes';
        }

        if ((int) $entry->min_billable === 0 && (int) $entry->max_billable === 0) {
            return 'No';
        }

        return 'Mixed';
    }

    private function mergedInvoiceStatus(object $entry): string
    {
        if ((int) $entry->max_billable === 0) {
            return 'Non-billable';
        }

        if ((int) $entry->min_invoiced === 1 && (int) $entry->max_invoiced === 1) {
            return 'Invoiced';
        }

        if ((int) $entry->min_invoiced === 0 && (int) $entry->max_invoiced === 0) {
            return 'Uninvoiced';
        }

        return 'Mixed';
    }
}
