<x-layouts.app meta-title="Clients · SoloHours">
    <div class="mb-4 flex items-center justify-between gap-2">
        <h1 class="text-lg font-semibold">Clients</h1>
        <button
            type="button"
            class="btn btn-primary btn-sm"
            onclick="document.getElementById('add-client-modal').showModal()"
        >
            Add client
        </button>
    </div>

    <dialog id="add-client-modal" class="modal">
        <div class="modal-box w-11/12 max-w-2xl">
<h3 class="mb-4 text-lg font-semibold">Add client</h3>
            <form method="POST" action="{{ route('clients.store') }}" class="grid gap-2">
                @csrf
                <label class="form-control">
                    <span class="label-text mb-1">Client name</span>
                    <input name="name" class="input input-bordered" placeholder="Client name" required>
                </label>
                <label class="form-control">
                    <span class="label-text mb-1">Notes</span>
                    <input name="notes" class="input input-bordered" placeholder="Notes">
                </label>
                <div class="modal-action">
                    <button class="btn btn-primary btn-sm">Add</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('add-client-modal').close()">Cancel</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button aria-label="Close"></button>
        </form>
    </dialog>

    <table class="table table-sm">
        <thead>
            <tr>
                <th>Name</th>
                <th>Projects</th>
                <th>Notes</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->projects_count }}</td>
                    <td>{{ $client->notes ?: "—" }}</td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                data-client-id="{{ $client->id }}"
                                data-client-name="{{ $client->name }}"
                                data-client-notes="{{ $client->notes ?? '' }}"
                                onclick="openEditClientModal(this)"
                            >
                                Edit
                            </button>
                            <form
                                method="POST"
                                action="{{ route('clients.destroy', $client->id) }}"
                                onsubmit="return confirm('Delete this client? All related projects and timer entries will be deleted permanently.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-error btn-sm text-error-content">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $clients->links() }}
    </div>

    <dialog id="edit-client-modal" class="modal">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="mb-4 text-lg font-semibold">Edit client</h3>
            <form id="edit-client-form" method="POST" class="grid gap-2" data-action-template="{{ route('clients.update', ['clientId' => '__CLIENT_ID__']) }}">
                @csrf
                @method('PATCH')
                <label class="form-control">
                    <span class="label-text mb-1">Client name</span>
                    <input id="edit-client-name" name="name" class="input input-bordered" required>
                </label>
                <label class="form-control">
                    <span class="label-text mb-1">Notes</span>
                    <input id="edit-client-notes" name="notes" class="input input-bordered" placeholder="Notes">
                </label>
                <div class="modal-action">
                    <button class="btn btn-primary btn-sm">Save</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('edit-client-modal').close()">Cancel</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button aria-label="Close"></button>
        </form>
    </dialog>

    <script>
        function openEditClientModal(button) {
            const modal = document.getElementById('edit-client-modal');
            const form = document.getElementById('edit-client-form');
            const actionTemplate = form.dataset.actionTemplate;

            form.action = actionTemplate.replace('__CLIENT_ID__', button.dataset.clientId);
            document.getElementById('edit-client-name').value = button.dataset.clientName || '';
            document.getElementById('edit-client-notes').value = button.dataset.clientNotes || '';

            modal.showModal();
        }
    </script>
</x-layouts.app>
