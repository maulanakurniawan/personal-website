<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle ?? 'SoloHours Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.google-analytics')
</head>
<body class="min-h-screen bg-base-100" data-theme="light">
@php
$adminBackend = (bool) ($adminBackend ?? false);
$navigationItems = $adminBackend
? [
    ['label' => 'Users', 'route' => route('admin.users.index')],
    ['label' => 'Subscriptions', 'route' => route('admin.subscriptions.index')],
    ['label' => 'Transactions', 'route' => route('admin.transactions.index')],
]
: [
    ['label' => 'Dashboard', 'route' => route('dashboard')],
    ['label' => 'Time Entries', 'route' => route('time-entries.index')],
    ['label' => 'Clients', 'route' => route('clients.index')],
    ['label' => 'Projects', 'route' => route('projects.index')],
    ['label' => 'Reports', 'route' => route('reports.index')],
];

$user = auth()->user();
$userInitial = mb_strtoupper(mb_substr(trim($user->name ?? 'User'), 0, 1));
$activeSubscription = $user?->activeSubscription()->first();
$activeSubscriptionName = $activeSubscription?->plan
    ? \App\Models\Plan::query()->where('internal_code', $activeSubscription->plan)->value('name')
    : null;
$headerProjects = $adminBackend ? collect() : $user->projects()->whereNull('locked_at')->with('client')->orderBy('name')->get();
$headerClients = $adminBackend ? collect() : $user->clients()->orderBy('name')->get();
$headerActiveTimer = $adminBackend ? null : $user->timeEntries()->with('project')->whereNull('ended_at')->latest('started_at')->first();
$canTrackTime = $adminBackend ? true : $user->canTrackTime();
@endphp
<div class="border-b border-base-200 bg-base-100">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5" aria-label="SoloHours home">
            <img src="/assets/logo.svg" alt="SoloHours" class="h-9 w-9 shrink-0" />
            <span class="text-base font-semibold tracking-tight">SoloHours</span>
        </a>

        <div class="flex items-center gap-2">
            @if(!$adminBackend)
                <button type="button" id="header-start-timer-btn" class="btn btn-primary btn-sm {{ $headerActiveTimer ? 'hidden' : '' }}" onclick="document.getElementById('global-start-timer-modal').showModal()" @disabled(! $canTrackTime)>Start Timer</button>
            @endif
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost normal-case px-2">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-8 text-xs font-semibold">
                            <span>{{ $userInitial !== '' ? $userInitial : 'U' }}</span>
                        </div>
                    </div>
                </label>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-2 z-[1] p-2 shadow bg-base-100 rounded-box w-56 border border-base-200">
                    <li class="menu-title"><span class="normal-case">{{ $user->name }}</span></li>
                    <li class="px-4 pb-2 text-xs text-base-content/70">{{ $activeSubscriptionName ?? 'No active subscription' }}</li>
                    <li class="my-1 border-t border-base-200"></li>
                    @if (! $adminBackend)
                        <li><a href="{{ route('profile.edit') }}" class="flex items-center h-10 rounded-lg">Profile</a></li>
                        <li><a href="{{ route('billing.history') }}" class="flex items-center h-10 rounded-lg">Subscriptions</a></li>
                        <li><a href="{{ route('transactions.index') }}" class="flex items-center h-10 rounded-lg">Transactions</a></li>
                        <li class="my-1 border-t border-base-200"></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full text-left">Logout</button></form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if($headerActiveTimer)
    <div class="fixed top-3 left-1/2 -translate-x-1/2 z-50" id="floating-running-timer" data-started-at="{{ $headerActiveTimer->started_at->timestamp }}" data-stop-url="{{ route('timer.stop') }}">
        <div class="flex items-center gap-3 rounded-full border border-base-300 bg-base-content text-base-100 px-4 py-2 shadow-lg transition-all duration-300 sh-timer-indicator-enter">
            <span class="text-sm text-base-100/80">{{ $headerActiveTimer->task ?: 'Running timer' }}</span>
            <span class="font-mono text-sm" data-elapsed>00:00:00</span>
            <button type="button" class="btn btn-error btn-sm" id="floating-stop-btn">Stop</button>
        </div>
    </div>
@endif

@php
$flashToasts = [];
if (session('success')) {
    $flashToasts[] = ['type' => 'success', 'message' => session('success')];
}
if (session('error')) {
    $flashToasts[] = ['type' => 'error', 'message' => session('error')];
}
if (session('status')) {
    $statusMessage = session('status') === 'verification-link-sent'
        ? 'A fresh verification link has been sent to your email address.'
        : session('status');
    $flashToasts[] = ['type' => 'success', 'message' => $statusMessage];
}
@endphp
@if($flashToasts)
    <div
        class="fixed left-1/2 -translate-x-1/2 z-[70] flex flex-col items-center gap-2 {{ $headerActiveTimer ? 'top-16' : 'top-3' }}"
        id="flash-toast-stack"
    >
        @foreach($flashToasts as $toast)
            <div class="sh-toast {{ $toast['type'] === 'error' ? 'sh-toast-error' : 'sh-toast-success' }}" role="status" data-toast>
                <span class="text-sm">{{ $toast['message'] }}</span>
                <button type="button" class="sh-toast-close" aria-label="Dismiss" data-toast-close>✕</button>
            </div>
        @endforeach
    </div>
@endif

@if(! $adminBackend && ! $canTrackTime)
    <div class="container mx-auto px-4 pt-4">
        <div class="alert alert-warning flex flex-wrap items-center gap-3">
            <span>You are currently on free access. Choose a plan to unlock time tracking.</span>
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('billing.checkout', ['plan' => 'starter']) }}">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-outline">Starter</button>
                </form>
                <form method="POST" action="{{ route('billing.checkout', ['plan' => 'pro']) }}">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-primary">Pro</button>
                </form>
            </div>
        </div>
    </div>
@endif

<div class="container mx-auto px-4 py-6 grid gap-6 lg:grid-cols-[200px_minmax(0,1fr)]">
    <aside class="space-y-1">@foreach($navigationItems as $item)<a href="{{ $item['route'] }}" class="btn btn-ghost btn-sm w-full justify-start">{{ $item['label'] }}</a>@endforeach</aside>
    <main>{{ $slot }}</main>
</div>

@if(!$adminBackend)
<dialog id="global-start-timer-modal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
<h3 class="mb-4 text-lg font-semibold">Start timer</h3>
        <form method="POST" action="{{ route('timer.start') }}" class="grid gap-2" id="global-timer-form">@csrf
            <label class="form-control">
                <span class="label-text mb-1">Task</span>
                <div class="relative">
                    <input name="task" id="global-task-input" class="input input-bordered" placeholder="What are you working on?" autocomplete="off">
                    <div id="global-task-results" class="absolute left-0 right-0 top-[calc(100%+0.25rem)] hidden rounded-md border border-base-200 bg-base-100 shadow-md z-30 max-h-56 overflow-y-auto"></div>
                </div>
            </label>
            <label class="form-control">
                <span class="label-text mb-1">Project</span>
                <select name="project_id" id="global-project-select" class="select select-bordered" required>
                    <option value="">Select project</option>
                    @foreach($headerProjects as $project)
                        <option value="{{ $project->id }}" data-client-id="{{ $project->client_id }}" data-client="{{ $project->client?->name ?? 'No client' }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text mb-1">Client</span>
                <select id="global-client-select" class="select select-bordered">
                    <option value="">Select client</option>
                    @foreach($headerClients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="form-control"><label class="label cursor-pointer justify-start gap-2 px-0"><input type="checkbox" id="global-billable-checkbox" name="is_billable" value="1" checked class="checkbox checkbox-sm"><span class="label-text">Billable</span></label></div>
            <div class="modal-action"><button class="btn btn-primary btn-sm" @disabled(! $canTrackTime)>Start Timer</button><button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('global-start-timer-modal').close()">Cancel</button></div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close"></button></form>
</dialog>
@endif

<script>
(() => {
    const ensureToastStack = () => {
        let stack = document.getElementById('flash-toast-stack');
        if (stack) return stack;

        stack = document.createElement('div');
        stack.id = 'flash-toast-stack';
        stack.className = 'fixed left-1/2 -translate-x-1/2 z-[70] flex flex-col items-center gap-2 top-3';
        document.body.appendChild(stack);
        return stack;
    };

    const syncToastStackOffset = () => {
        const stack = ensureToastStack();
        const hasFloatingTimer = Boolean(document.getElementById('floating-running-timer'));
        stack.classList.toggle('top-3', !hasFloatingTimer);
        stack.classList.toggle('top-16', hasFloatingTimer);
    };

    const showToast = (message, type = 'success') => {
        if (!message) return;

        const stack = ensureToastStack();
        syncToastStackOffset();
        const toast = document.createElement('div');
        toast.className = `sh-toast ${type === 'error' ? 'sh-toast-error' : 'sh-toast-success'}`;
        toast.setAttribute('role', 'status');
        toast.setAttribute('data-toast', '');
        toast.innerHTML = `<span class=\"text-sm\">${message}</span><button type=\"button\" class=\"sh-toast-close\" aria-label=\"Dismiss\" data-toast-close>✕</button>`;
        stack.appendChild(toast);

        const removeToast = () => {
            toast.classList.add('opacity-0', '-translate-y-2');
            window.setTimeout(() => {
                toast.remove();
                syncToastStackOffset();
            }, 180);
        };

        window.setTimeout(removeToast, 3800);
        toast.querySelector('[data-toast-close]')?.addEventListener('click', removeToast);
    };

    const formatElapsed = (s) => {
        const h = String(Math.floor(s / 3600)).padStart(2, '0');
        const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
        const sec = String(s % 60).padStart(2, '0');
        return `${h}:${m}:${sec}`;
    };

    const indicator = document.getElementById('floating-running-timer');
    syncToastStackOffset();
    if (indicator) {
        const startedAt = Number(indicator.dataset.startedAt || 0);
        const elapsedEl = indicator.querySelector('[data-elapsed]');
        const stopBtn = document.getElementById('floating-stop-btn');
        const startBtn = document.getElementById('header-start-timer-btn');
        const tick = () => {
            if (!elapsedEl || !startedAt) return;
            const elapsed = Math.max(0, Math.floor(Date.now() / 1000) - startedAt);
            elapsedEl.textContent = formatElapsed(elapsed);
        };
        tick();
        setInterval(tick, 1000);

        stopBtn?.addEventListener('click', async () => {
            stopBtn.disabled = true;
            try {
                const response = await fetch(indicator.dataset.stopUrl || '', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });

                if (!response.ok) {
                    stopBtn.disabled = false;
                    return;
                }

                indicator.firstElementChild?.classList.remove('sh-timer-indicator-enter');
                indicator.firstElementChild?.classList.add('sh-timer-indicator-exit');
                window.setTimeout(() => {
                    indicator.remove();
                    syncToastStackOffset();
                }, 220);

                if (startBtn) {
                    startBtn.classList.remove('hidden');
                }

                showToast('Timer stopped.');
                window.dispatchEvent(new CustomEvent('timer:stopped'));
            } catch (error) {
                stopBtn.disabled = false;
            }
        });
    }

    const projectSelect = document.getElementById('global-project-select');
    const clientSelect = document.getElementById('global-client-select');
    const taskInput = document.getElementById('global-task-input');
    const taskResults = document.getElementById('global-task-results');
    const billableCheckbox = document.getElementById('global-billable-checkbox');

    const syncClientFromProject = () => {
        if (!projectSelect || !clientSelect) return;
        const selected = projectSelect.selectedOptions[0];
        if (selected?.dataset.clientId) {
            clientSelect.value = selected.dataset.clientId;
        }
    };

    projectSelect?.addEventListener('change', syncClientFromProject);
    syncClientFromProject();

    const renderGlobalSuggestions = (tasks) => {
        if (!taskResults) return;
        if (tasks.length === 0) {
            taskResults.classList.add('hidden');
            taskResults.innerHTML = '';
            return;
        }

        taskResults.innerHTML = tasks.map((task) => `
            <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-base-200" data-task="${task.task.replace(/"/g, '&quot;')}" data-project-id="${task.project_id}" data-client-name="${task.client_name}" data-billable="${task.is_billable ? '1' : '0'}">
                <div class="font-medium">${task.task}</div>
                <div class="text-xs text-base-content/70">${task.project_name} · ${task.client_name}</div>
            </button>
        `).join('');
        taskResults.classList.remove('hidden');
    };

    let globalDebounce;
    taskInput?.addEventListener('input', () => {
        const query = taskInput.value.trim();
        window.clearTimeout(globalDebounce);

        if (query.length < 3) {
            renderGlobalSuggestions([]);
            return;
        }

        globalDebounce = window.setTimeout(async () => {
            try {
                const response = await fetch(`{{ route('timer.task-suggestions') }}?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();
                renderGlobalSuggestions(Array.isArray(data.tasks) ? data.tasks : []);
            } catch (error) {
                renderGlobalSuggestions([]);
            }
        }, 250);
    });

    taskResults?.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-task]');
        if (!button || !taskInput || !projectSelect) return;

        taskInput.value = button.dataset.task || '';
        projectSelect.value = button.dataset.projectId || '';
        syncClientFromProject();
        if (billableCheckbox) {
            billableCheckbox.checked = button.dataset.billable === '1';
        }
        taskResults.classList.add('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!taskResults || !taskInput) return;
        if (taskResults.contains(event.target) || taskInput.contains(event.target)) return;
        taskResults.classList.add('hidden');
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const removeToast = () => {
            toast.classList.add('opacity-0', '-translate-y-2');
            window.setTimeout(() => {
                toast.remove();
                syncToastStackOffset();
            }, 180);
        };

        window.setTimeout(removeToast, 3800);
        toast.querySelector('[data-toast-close]')?.addEventListener('click', removeToast);
    });
})();
</script>
</body>
</html>
