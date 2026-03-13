<x-layouts.app meta-title="Projects · SoloHours">
    <div class="mb-4 flex items-center justify-between gap-2">
        <h1 class="text-lg font-semibold">Projects</h1>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('add-project-modal').showModal()">Add Project</button>
    </div>

    <dialog id="add-project-modal" class="modal">
        <div class="modal-box w-11/12 max-w-2xl">
<h3 class="mb-4 text-lg font-semibold">Add project</h3>
            <form method="POST" action="{{ route('projects.store') }}" class="grid gap-2">
                @csrf
                <label class="form-control"><span class="label-text mb-1">Project name</span><input name="name" class="input input-bordered" placeholder="Project" required></label>
                <label class="form-control"><span class="label-text mb-1">Client</span><select name="client_id" class="select select-bordered"><option value="">No client</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select></label>
                <label class="form-control"><span class="label-text mb-1">Hourly rate</span><input name="hourly_rate" type="number" step="0.01" min="0" class="input input-bordered" placeholder="e.g. 85.00"></label>
                <div class="form-control"><label class="label cursor-pointer justify-start gap-2 px-0"><input type="checkbox" name="rounding_enabled" value="1" class="checkbox checkbox-sm"><span class="label-text">Enable rounding</span></label></div>
                <label class="form-control"><span class="label-text mb-1">Rounding unit</span><select name="rounding_unit_minutes" class="select select-bordered"><option value="">No rounding unit</option><option value="5">5 minutes</option><option value="10">10 minutes</option><option value="15">15 minutes</option><option value="30">30 minutes</option></select></label>
                <label class="form-control"><span class="label-text mb-1">Notes</span><input name="notes" class="input input-bordered" placeholder="Notes"></label>
                <div class="modal-action"><button class="btn btn-primary btn-sm">Add Project</button><button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('add-project-modal').close()">Cancel</button></div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button aria-label="Close"></button></form>
    </dialog>

    <table class="table table-sm">
        <thead><tr><th>Project</th><th>Client</th><th>Rate</th><th>Rounding</th><th>Notes</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
            @foreach($projects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->client?->name ?? 'No client' }}</td>
                    <td>{{ $project->hourly_rate !== null ? '$'.number_format($project->hourly_rate, 2) : '—' }}</td>
                    <td>{{ $project->rounding_enabled && $project->rounding_unit_minutes ? $project->rounding_unit_minutes.' min' : '—' }}</td>
                    <td>{{ $project->notes }}</td>
                    <td>@if($project->locked_at)<span class="sh-pill sh-pill-warning">Locked</span>@else<span class="sh-pill sh-pill-positive">Active</span>@endif</td>
                    <td class="text-right"><div class="inline-flex items-center gap-2"><button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                data-project-id="{{ $project->id }}"
                                data-project-name="{{ $project->name }}"
                                data-project-client-id="{{ $project->client_id ?? '' }}"
                                data-project-hourly-rate="{{ $project->hourly_rate ?? '' }}"
                                data-project-rounding-enabled="{{ $project->rounding_enabled ? '1' : '0' }}"
                                data-project-rounding-unit="{{ $project->rounding_unit_minutes ?? '' }}"
                                data-project-notes="{{ $project->notes ?? '' }}"
                                onclick="openEditProjectModal(this)"
                            >Edit</button><form method="POST" action="{{ route('projects.destroy', $project->id) }}" onsubmit="return confirm('Delete this project? All related timer entries will be deleted permanently.')">@csrf @method('DELETE')<button class="btn btn-error btn-sm text-error-content">Delete</button></form></div></td>
                </tr>


            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $projects->links() }}</div>

    <dialog id="edit-project-modal" class="modal">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="mb-4 text-lg font-semibold">Edit project</h3>
            <form id="edit-project-form" method="POST" class="grid gap-2" data-action-template="{{ route('projects.update', ['projectId' => '__PROJECT_ID__']) }}">@csrf @method('PATCH')
                <label class="form-control"><span class="label-text mb-1">Project name</span><input id="edit-project-name" name="name" class="input input-bordered" required></label>
                <label class="form-control"><span class="label-text mb-1">Client</span><select id="edit-project-client-id" name="client_id" class="select select-bordered"><option value="">No client</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select></label>
                <label class="form-control"><span class="label-text mb-1">Hourly rate</span><input id="edit-project-hourly-rate" name="hourly_rate" type="number" step="0.01" min="0" class="input input-bordered"></label>
                <div class="form-control"><label class="label cursor-pointer justify-start gap-2 px-0"><input id="edit-project-rounding-enabled" type="checkbox" name="rounding_enabled" value="1" class="checkbox checkbox-sm"><span class="label-text">Enable rounding</span></label></div>
                <label class="form-control"><span class="label-text mb-1">Rounding unit</span><select id="edit-project-rounding-unit-minutes" name="rounding_unit_minutes" class="select select-bordered"><option value="">No rounding unit</option>@foreach([5,10,15,30] as $unit)<option value="{{ $unit }}">{{ $unit }} minutes</option>@endforeach</select></label>
                <label class="form-control"><span class="label-text mb-1">Notes</span><input id="edit-project-notes" name="notes" class="input input-bordered" placeholder="Notes"></label>
                <div class="modal-action"><button class="btn btn-primary btn-sm">Save</button><button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('edit-project-modal').close()">Cancel</button></div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button aria-label="Close"></button></form>
    </dialog>

    <script>
        function openEditProjectModal(button) {
            const modal = document.getElementById('edit-project-modal');
            const form = document.getElementById('edit-project-form');
            const actionTemplate = form.dataset.actionTemplate;

            form.action = actionTemplate.replace('__PROJECT_ID__', button.dataset.projectId);
            document.getElementById('edit-project-name').value = button.dataset.projectName || '';
            document.getElementById('edit-project-client-id').value = button.dataset.projectClientId || '';
            document.getElementById('edit-project-hourly-rate').value = button.dataset.projectHourlyRate || '';
            document.getElementById('edit-project-rounding-enabled').checked = button.dataset.projectRoundingEnabled === '1';
            document.getElementById('edit-project-rounding-unit-minutes').value = button.dataset.projectRoundingUnit || '';
            document.getElementById('edit-project-notes').value = button.dataset.projectNotes || '';

            modal.showModal();
        }
    </script>

</x-layouts.app>
