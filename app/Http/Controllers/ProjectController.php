<?php

namespace App\Http\Controllers;

use App\Services\ProjectLockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request, ProjectLockService $lockService): View
    {
        $lockService->enforce($request->user());

        return view('projects.index', [
            'projects' => $request->user()->projects()->with('client')->latest()->paginate(25),
            'clients' => $request->user()->clients()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ProjectLockService $lockService): RedirectResponse
    {
        $projectLimit = $request->user()->projectLimit();

        if ($projectLimit !== null && $request->user()->projects()->count() >= $projectLimit) {
            return back()->with('error', "Starter includes up to {$projectLimit} projects. Upgrade to Pro for unlimited projects.");
        }

        $project = $request->user()->projects()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('user_id', $request->user()->id)],
            'notes' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'rounding_enabled' => ['nullable', 'boolean'],
            'rounding_unit_minutes' => ['nullable', Rule::in([5, 10, 15, 30])],
        ]));

        if (! $request->boolean('rounding_enabled')) {
            $project->update(['rounding_enabled' => false, 'rounding_unit_minutes' => null]);
        }
        $lockService->enforce($request->user());

        return back()
            ->with('success', 'Project saved.')
            ->with('new_project_id', $project->id);
    }

    public function update(Request $request, int $projectId, ProjectLockService $lockService): RedirectResponse
    {
        $project = $request->user()->projects()->findOrFail($projectId);
        $project->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('user_id', $request->user()->id)],
            'notes' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'rounding_enabled' => ['nullable', 'boolean'],
            'rounding_unit_minutes' => ['nullable', Rule::in([5, 10, 15, 30])],
        ]));

        if (! $request->boolean('rounding_enabled')) {
            $project->update(['rounding_enabled' => false, 'rounding_unit_minutes' => null]);
        }
        $lockService->enforce($request->user());

        return back()->with('success', 'Project updated.');
    }

    public function destroy(Request $request, int $projectId): RedirectResponse
    {
        $request->user()->projects()->findOrFail($projectId)->delete();

        return back()->with('success', 'Project deleted.');
    }
}
