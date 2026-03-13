<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;

class ProjectLockService
{
    public function enforce(User $user): void
    {
        $limit = $user->projectLimit();

        if ($limit === null) {
            $user->projects()->whereNotNull('locked_at')->update(['locked_at' => null]);

            return;
        }

        $projects = $user->projects()
            ->withMax('timeEntries', 'started_at')
            ->get()
            ->sortByDesc(fn (Project $project) => $project->time_entries_max_started_at ?? $project->last_used_at ?? $project->updated_at)
            ->values();

        $active = $projects->take($limit)->pluck('id');

        $user->projects()->whereIn('id', $active)->update(['locked_at' => null]);
        $user->projects()->whereNotIn('id', $active)->update(['locked_at' => now()]);
    }
}
