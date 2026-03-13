<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $todaySeconds = $user->timeEntries()->whereDate('started_at', now()->toDateString())->sum('duration_seconds');
        $weekSeconds = $user->timeEntries()->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('duration_seconds');
        $monthSeconds = $user->timeEntries()->whereBetween('started_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('duration_seconds');

        return view('dashboard.index', [
            'todaySeconds' => $todaySeconds,
            'weekSeconds' => $weekSeconds,
            'monthSeconds' => $monthSeconds,
            'activeTimer' => $user->timeEntries()->with('project')->whereNull('ended_at')->latest('started_at')->first(),
            'recentEntries' => $user->timeEntries()->with('project.client')->latest('started_at')->limit(10)->get(),
        ]);
    }
}
