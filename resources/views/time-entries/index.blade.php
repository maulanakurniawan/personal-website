<x-layouts.app meta-title="Time Entries · SoloHours">
    @php
        $locale = auth()->user()?->locale ?? app()->getLocale();
        $timezone = auth()->user()?->timezone ?? config('app.timezone');
        $formatDuration = static fn (int $seconds): string => sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
    @endphp

    <h1 class="text-lg font-semibold mb-4">Time Entries</h1>

    @unless($canTrackTime)
        <div class="alert alert-warning mb-4">
            <span>Time tracking is disabled on your account. Subscribe to enable Start/Stop timers.</span>
            <a href="{{ route('billing.history') }}" class="btn btn-xs btn-warning">Subscribe</a>
        </div>
    @endunless

    <div class="mb-4 rounded-lg border border-base-200 p-3">
        <form method="POST" action="{{ $activeTimer ? route('timer.stop') : route('timer.start') }}" class="space-y-4" id="timer-start-form" data-start-action="{{ route('timer.start') }}" data-stop-action="{{ route('timer.stop') }}">@csrf
            @php
                $activeElapsed = $activeTimer ? now()->diffInSeconds($activeTimer->started_at) : 0;
                $activeProjectId = $activeTimer?->project_id ?? session('new_project_id');
                $activeClientName = $projects->firstWhere('id', $activeProjectId)?->client?->name ?? '';
                $lockedControlClass = $activeTimer ? 'bg-base-200 text-base-content/60 border-base-300' : '';
                $lockedTimerClass = $activeTimer ? 'bg-base-200 text-base-content/60' : 'bg-transparent';
            @endphp
            <input
                type="text"
                id="timer-initial-display"
                inputmode="numeric"
                value="{{ sprintf('%02d:%02d:%02d', intdiv($activeElapsed, 3600), intdiv($activeElapsed % 3600, 60), $activeElapsed % 60) }}"
                class="input w-64 h-20 border-0 px-0 text-5xl font-normal font-mono focus:outline-none focus:border-0 {{ $lockedTimerClass }}"
                aria-label="Initial timer"
                autocomplete="off"
                @disabled($activeTimer)
                data-started-at="{{ $activeTimer?->started_at?->timestamp }}"
            >
            <input type="hidden" name="initial_seconds" id="timer-initial-seconds" value="{{ $activeElapsed }}">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1.3fr)_minmax(0,1.3fr)_auto_auto] xl:items-center">
                    <div class="relative">
                        <input
                            type="text"
                            name="task"
                            id="timer-task-input"
                            class="input input-bordered w-full {{ $lockedControlClass }}"
                            placeholder="What are you working on?"
                            autocomplete="off"
                            value="{{ $activeTimer?->task }}"
                            @disabled($activeTimer)
                        >
                        <div id="timer-task-results" class="absolute left-0 right-0 top-[calc(100%+0.25rem)] hidden rounded-md border border-base-200 bg-base-100 shadow-md z-30 max-h-56 overflow-y-auto"></div>
                    </div>

                    <div class="join w-full">
                        <select name="project_id" id="timer-project-select" class="select select-bordered join-item w-full {{ $lockedControlClass }}" required @disabled($activeTimer)>
                            <option value="">Select project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" data-client="{{ $project->client?->name ?? 'No client' }}" @selected($activeProjectId == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline btn-sm join-item" id="timer-add-project-btn" aria-label="Add project" onclick="document.getElementById('add-project-modal').showModal()" @disabled($activeTimer)>+</button>
                    </div>

                    <div class="join w-full">
                        <input id="timer-client-display" class="input input-bordered join-item w-full {{ $lockedControlClass }}" value="{{ $activeClientName }}" placeholder="Select client" readonly @disabled($activeTimer)>
                        <button type="button" class="btn btn-outline btn-sm join-item" id="timer-add-client-btn" aria-label="Add client" onclick="document.getElementById('add-client-modal').showModal()" @disabled($activeTimer)>+</button>
                    </div>

                    <label class="label cursor-pointer justify-start gap-2 sm:col-span-1 h-12 {{ $activeTimer ? 'opacity-70' : '' }}"><input type="checkbox" name="is_billable" value="1" class="checkbox checkbox-sm" id="timer-billable-checkbox" @checked($activeTimer ? $activeTimer->is_billable : true) @disabled($activeTimer)><span class="label-text">Billable</span></label>
                    <button id="timer-submit-button" class="btn btn-sm sm:col-span-1 {{ $activeTimer ? 'btn-error' : 'btn-primary' }}" @disabled((!$activeTimer && $projects->isEmpty()) || ! $canTrackTime)>{{ $activeTimer ? 'Stop' : 'Start' }}</button>
            </div>
        </form>
    </div>



<dialog id="add-project-modal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
<h3 class="mb-4 text-lg font-semibold">New project</h3>
        <form method="POST" action="{{ route('projects.store') }}" class="grid gap-2">@csrf
            <label class="form-control">
                <span class="label-text mb-1">Project name</span>
                <input name="name" class="input input-bordered {{ $lockedControlClass }}" placeholder="Project name" required>
            </label>
            <label class="form-control">
                <span class="label-text mb-1">Client</span>
                <select name="client_id" class="select select-bordered">
                    <option value="">Select client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(session('new_client_id') == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text mb-1">Notes</span>
                <textarea name="notes" class="textarea textarea-bordered" placeholder="Notes (optional)"></textarea>
            </label>
            <div class="modal-action">
                <button class="btn btn-primary btn-sm">Save project</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('add-project-modal').close()">Cancel</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close"></button></form>
</dialog>

<dialog id="add-client-modal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
<h3 class="mb-4 text-lg font-semibold">New client</h3>
        <form method="POST" action="{{ route('clients.store') }}" class="grid gap-2">@csrf
            <label class="form-control">
                <span class="label-text mb-1">Client name</span>
                <input name="name" class="input input-bordered {{ $lockedControlClass }}" placeholder="Client name" required>
            </label>
            <label class="form-control">
                <span class="label-text mb-1">Notes</span>
                <textarea name="notes" class="textarea textarea-bordered" placeholder="Notes (optional)"></textarea>
            </label>
            <div class="modal-action">
                <button class="btn btn-primary btn-sm">Save client</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('add-client-modal').close()">Cancel</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close"></button></form>
</dialog>

    <table class="table table-sm"><thead><tr><th>Date</th><th>Project</th><th>Task</th><th>Duration</th><th>Status</th><th></th></tr></thead><tbody>
    @foreach($entries as $entry)
        <tr>
            <td>{{ $entry->started_at->clone()->timezone($timezone)->locale($locale)->isoFormat('LLL') }}</td>
            <td>{{ $entry->project->name }}</td>
            <td class="max-w-sm break-words text-sm text-base-content/80">{{ $entry->task ?: '—' }}</td>
            <td>{{ $formatDuration($entry->duration_seconds) }}</td>
            <td><span class="sh-pill {{ $entry->is_billable ? 'sh-pill-billable' : 'sh-pill-muted' }}">{{ $entry->is_billable ? 'Billable':'Non-billable' }}</span> @if($entry->invoiced_at)<span class="sh-pill sh-pill-invoiced">Invoiced</span>@else<span class="sh-pill sh-pill-uninvoiced">Uninvoiced</span>@endif</td>
            <td>
                <div class="flex items-center justify-end gap-2">
                    @if(!$activeTimer && !$entry->project->isLocked())
                        <form method="POST" action="{{ route('timer.continue', $entry->id) }}">@csrf<button class="btn btn-ghost btn-sm" @disabled(! $canTrackTime)>Continue</button></form>
                    @endif
                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-entry-id="{{ $entry->id }}"
                        data-entry-started-at="{{ $entry->started_at?->clone()->timezone($timezone)->format('Y-m-d\TH:i') }}"
                        data-entry-ended-at="{{ $entry->ended_at?->clone()->timezone($timezone)->format('Y-m-d\TH:i') }}"
                        data-entry-task="{{ $entry->task ?? '' }}"
                        data-entry-is-billable="{{ $entry->is_billable ? '1' : '0' }}"
                        data-entry-invoiced="{{ $entry->invoiced_at !== null ? '1' : '0' }}"
                        onclick="openEditEntryModal(this)"
                    >Edit</button>
                    <form method="POST" action="{{ route('time-entries.destroy', $entry->id) }}" onsubmit="return confirm('Delete this entry?')">@csrf @method('DELETE')<button class="btn btn-error btn-sm text-error-content">Delete</button></form>
                </div>
            </td>
        </tr>


    @endforeach
    </tbody></table>
    {{ $entries->links() }}

<dialog id="edit-entry-modal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
<h3 class="mb-4 text-lg font-semibold">Edit entry</h3>
        <form id="edit-entry-form" method="POST" class="grid gap-2" data-action-template="{{ route('time-entries.update', ['entryId' => '__ENTRY_ID__']) }}">@csrf @method('PATCH')
            <label class="form-control">
                <span class="label-text mb-1">Start time</span>
                <input id="edit-entry-started-at" type="datetime-local" name="started_at" class="input input-bordered w-full {{ $lockedControlClass }}">
            </label>
            <label class="form-control">
                <span class="label-text mb-1">End time</span>
                <input id="edit-entry-ended-at" type="datetime-local" name="ended_at" class="input input-bordered w-full {{ $lockedControlClass }}">
            </label>
            <label class="form-control">
                <span class="label-text mb-1">Task</span>
                <input id="edit-entry-task" name="task" class="input input-bordered w-full {{ $lockedControlClass }}" placeholder="Task">
            </label>
            <div class="form-control"><label class="label cursor-pointer justify-start gap-2 px-0"><input id="edit-entry-billable" type="checkbox" name="is_billable" value="1" class="checkbox checkbox-sm"><span class="label-text">Billable</span></label></div>
            <div class="form-control"><label class="label cursor-pointer justify-start gap-2 px-0"><input id="edit-entry-invoiced" type="checkbox" name="invoiced" value="1" class="checkbox checkbox-sm"><span class="label-text">Invoiced</span></label></div>
            <div class="modal-action">
                <button class="btn btn-primary btn-sm">Save changes</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('edit-entry-modal').close()">Cancel</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close"></button></form>
</dialog>


<script>
function openEditEntryModal(button) {
    const modal = document.getElementById('edit-entry-modal');
    const form = document.getElementById('edit-entry-form');
    const actionTemplate = form.dataset.actionTemplate;

    form.action = actionTemplate.replace('__ENTRY_ID__', button.dataset.entryId || '');
    document.getElementById('edit-entry-started-at').value = button.dataset.entryStartedAt || '';
    document.getElementById('edit-entry-ended-at').value = button.dataset.entryEndedAt || '';
    document.getElementById('edit-entry-task').value = button.dataset.entryTask || '';
    document.getElementById('edit-entry-billable').checked = button.dataset.entryIsBillable === '1';
    document.getElementById('edit-entry-invoiced').checked = button.dataset.entryInvoiced === '1';

    modal.showModal();
}

(() => {
    const taskInput = document.getElementById('timer-task-input');
    const taskResults = document.getElementById('timer-task-results');
    const select = document.getElementById('timer-project-select');
    const client = document.getElementById('timer-client-display');
    const billableCheckbox = document.getElementById('timer-billable-checkbox');
    const timerDisplay = document.getElementById('timer-initial-display');
    const timerSeconds = document.getElementById('timer-initial-seconds');
    if (!select) return;

    const updateClient = () => {
        const selected = select.selectedOptions[0];
        if (client) {
            client.value = selected?.dataset.client || '';
        }
    };

    select.addEventListener('change', updateClient);
    updateClient();

    const renderSuggestions = (tasks) => {
        if (!taskResults) return;

        if (tasks.length === 0) {
            taskResults.classList.add('hidden');
            taskResults.innerHTML = '';
            return;
        }

        taskResults.innerHTML = tasks.map((task) => `
            <button
                type="button"
                class="block w-full px-3 py-2 text-left text-sm hover:bg-base-200"
                data-task="${task.task.replace(/"/g, '&quot;')}"
                data-project-id="${task.project_id}"
                data-billable="${task.is_billable ? '1' : '0'}"
            >
                <div class="font-medium">${task.task}</div>
                <div class="text-xs text-base-content/70">${task.project_name} · ${task.client_name}</div>
            </button>
        `).join('');
        taskResults.classList.remove('hidden');
    };

    let debounceId;
    taskInput?.addEventListener('input', () => {
        const query = taskInput.value.trim();
        window.clearTimeout(debounceId);

        if (query.length < 2) {
            renderSuggestions([]);
            return;
        }

        debounceId = window.setTimeout(async () => {
            try {
                const response = await fetch(`{{ route('timer.task-suggestions') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                renderSuggestions(Array.isArray(data.tasks) ? data.tasks : []);
            } catch (error) {
                renderSuggestions([]);
            }
        }, 250);
    });

    taskResults?.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-task]');
        if (!button || !taskInput) return;

        taskInput.value = button.dataset.task || '';
        select.value = button.dataset.projectId || '';
        if (billableCheckbox) {
            billableCheckbox.checked = button.dataset.billable === '1';
        }
        updateClient();
        taskResults.classList.add('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!taskResults || !taskInput) return;
        if (taskResults.contains(event.target) || taskInput.contains(event.target)) return;
        taskResults.classList.add('hidden');
    });

    const setSecondsFromValue = () => {
        if (!timerDisplay || !timerSeconds) return;
        const digits = timerDisplay.value.replace(/\D/g, '').slice(0, 6).padStart(6, '0');
        const h = Number(digits.slice(0, 2));
        const m = Math.min(59, Number(digits.slice(2, 4)));
        const sec = Math.min(59, Number(digits.slice(4, 6)));
        timerSeconds.value = String((h * 3600) + (m * 60) + sec);
    };

    const startedAt = Number(timerDisplay?.dataset.startedAt || 0);
    const timerForm = document.getElementById('timer-start-form');
    const submitButton = document.getElementById('timer-submit-button');
    const addProjectButton = document.getElementById('timer-add-project-btn');
    const addClientButton = document.getElementById('timer-add-client-btn');
    let lockedTimerIntervalId;

    const segmentStarts = [0, 3, 6];
    const segmentEnds = [1, 4, 7];

    const ensureMaskedValue = () => {
        if (!timerDisplay) return;
        const digits = timerDisplay.value.replace(/\D/g, '').slice(0, 6).padEnd(6, '0');
        timerDisplay.value = `${digits.slice(0, 2)}:${digits.slice(2, 4)}:${digits.slice(4, 6)}`;
    };

    const segmentFromPosition = (position) => {
        if (position <= 2) return 0;
        if (position <= 5) return 1;
        return 2;
    };

    const setCaretToSegment = (segmentIndex) => {
        if (!timerDisplay) return;
        timerDisplay.setSelectionRange(segmentStarts[segmentIndex], segmentEnds[segmentIndex] + 1);
    };

    const writeDigitAtCaret = (digit) => {
        if (!timerDisplay) return;
        const caret = timerDisplay.selectionStart ?? 0;
        const segment = segmentFromPosition(caret);
        const valueChars = timerDisplay.value.split('');
        const writeIndex = caret === segmentStarts[segment] ? segmentStarts[segment] : segmentEnds[segment];
        valueChars[writeIndex] = digit;
        timerDisplay.value = valueChars.join('');

        if (writeIndex === segmentStarts[segment]) {
            timerDisplay.setSelectionRange(writeIndex + 1, writeIndex + 1);
            return;
        }

        const nextSegment = Math.min(segment + 1, 2);
        setCaretToSegment(nextSegment);
    };

    const bindEditableTimerMask = () => {
        if (!timerDisplay || timerDisplay.dataset.maskBound === '1') return;
        timerDisplay.dataset.maskBound = '1';

        timerDisplay.addEventListener('focus', () => {
            setCaretToSegment(0);
        });

        timerDisplay.addEventListener('click', () => {
            const caret = timerDisplay.selectionStart ?? 0;
            setCaretToSegment(segmentFromPosition(caret));
        });

        timerDisplay.addEventListener('keydown', (event) => {
            if (timerDisplay.disabled) return;
            if (event.key === 'Tab') return;

            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                const currentSegment = segmentFromPosition(timerDisplay.selectionStart ?? 0);
                const next = event.key === 'ArrowLeft' ? Math.max(0, currentSegment - 1) : Math.min(2, currentSegment + 1);
                setCaretToSegment(next);
                return;
            }

            if (event.key === 'Backspace' || event.key === 'Delete') {
                event.preventDefault();
                const segment = segmentFromPosition(timerDisplay.selectionStart ?? 0);
                const valueChars = timerDisplay.value.split('');
                valueChars[segmentStarts[segment]] = '0';
                valueChars[segmentEnds[segment]] = '0';
                timerDisplay.value = valueChars.join('');
                setCaretToSegment(segment);
                setSecondsFromValue();
                return;
            }

            if (!/^\d$/.test(event.key)) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            writeDigitAtCaret(event.key);
            setSecondsFromValue();
        });

        timerDisplay.addEventListener('blur', () => {
            const digits = timerDisplay.value.replace(/\D/g, '').slice(0, 6).padStart(6, '0');
            const h = digits.slice(0, 2);
            const m = String(Math.min(59, Number(digits.slice(2, 4)))).padStart(2, '0');
            const sec = String(Math.min(59, Number(digits.slice(4, 6)))).padStart(2, '0');
            timerDisplay.value = `${h}:${m}:${sec}`;
            setSecondsFromValue();
        });
    };

    const unlockTimerForm = () => {
        if (!timerForm || !timerDisplay) return;

        if (lockedTimerIntervalId) {
            window.clearInterval(lockedTimerIntervalId);
            lockedTimerIntervalId = undefined;
        }

        timerDisplay.disabled = false;
        timerDisplay.dataset.startedAt = '';
        timerDisplay.classList.remove('bg-base-200', 'text-base-content/60');
        timerDisplay.classList.add('bg-transparent');

        [taskInput, select, client, billableCheckbox, addProjectButton, addClientButton].forEach((el) => {
            if (!el) return;
            el.disabled = false;
            el.classList.remove('bg-base-200', 'text-base-content/60', 'border-base-300');
        });

        submitButton?.classList.remove('btn-error');
        submitButton?.classList.add('btn-primary');
        if (submitButton) {
            submitButton.textContent = 'Start';
        }
        if (timerForm.dataset.startAction) {
            timerForm.action = timerForm.dataset.startAction;
        }
    };

    if (timerDisplay && startedAt > 0) {
        const tickLockedTimer = () => {
            const elapsed = Math.max(0, Math.floor(Date.now() / 1000) - startedAt);
            const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
            const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
            const sec = String(elapsed % 60).padStart(2, '0');
            timerDisplay.value = `${h}:${m}:${sec}`;
            if (timerSeconds) {
                timerSeconds.value = String(elapsed);
            }
        };

        tickLockedTimer();
        lockedTimerIntervalId = window.setInterval(tickLockedTimer, 1000);
    }

    ensureMaskedValue();
    setSecondsFromValue();
    bindEditableTimerMask();

    window.addEventListener('timer:stopped', () => {
        unlockTimerForm();
        if (timerDisplay) {
            timerDisplay.value = '00:00:00';
        }
        if (timerSeconds) {
            timerSeconds.value = '0';
        }

        window.location.reload();
    });})();
</script>
</x-layouts.app>
