<?php

namespace App\Console\Commands;

use App\Mail\WeeklySummaryMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklySummaryReport extends Command
{
    protected $signature = 'reports:weekly-summary';

    protected $description = 'Send weekly tracked-time summaries to users with recent activity.';

    public function handle(): int
    {
        $from = now()->subDays(7)->startOfDay();
        $to = now();

        User::query()->chunkById(100, function ($users) use ($from, $to) {
            foreach ($users as $user) {
                $entries = $user->timeEntries()
                    ->with('project')
                    ->whereBetween('started_at', [$from, $to])
                    ->whereNotNull('ended_at')
                    ->get();

                if ($entries->isEmpty()) {
                    continue;
                }

                $totalSeconds = (int) $entries->sum('duration_seconds');
                $billableSeconds = (int) $entries->where('is_billable', true)->sum('duration_seconds');
                $estimatedRevenue = 0.0;
                $hasRevenue = false;

                foreach ($entries->where('is_billable', true) as $entry) {
                    if ($entry->project?->hourly_rate === null) {
                        continue;
                    }

                    $hasRevenue = true;
                    $seconds = $entry->duration_seconds;
                    if ($entry->project->rounding_enabled && $entry->project->rounding_unit_minutes) {
                        $unitSeconds = $entry->project->rounding_unit_minutes * 60;
                        $seconds = (int) (ceil($seconds / $unitSeconds) * $unitSeconds);
                    }

                    $estimatedRevenue += ((float) $entry->project->hourly_rate) * ($seconds / 3600);
                }

                $topProjects = $entries
                    ->groupBy(fn ($entry) => $entry->project?->name ?? 'Unassigned')
                    ->map(fn ($group) => (int) $group->sum('duration_seconds'))
                    ->sortDesc()
                    ->take(3);

                Mail::to($user->email)->send(new WeeklySummaryMail(
                    userName: $user->name,
                    totalSeconds: $totalSeconds,
                    billableSeconds: $billableSeconds,
                    estimatedRevenue: $hasRevenue ? round($estimatedRevenue, 2) : null,
                    topProjects: $topProjects,
                ));
            }
        });

        $this->info('Weekly summaries sent.');

        return self::SUCCESS;
    }
}
