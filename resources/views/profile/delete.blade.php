<x-layouts.app>
    <div class="max-w-2xl">
        <div class="card bg-base-100 border border-error/40 shadow-sm">
            <div class="card-body">
                <h1 class="text-xl font-semibold text-error">Confirm account deletion</h1>
                <p class="text-sm text-base-content/70">This action cannot be undone. Deleting your account will:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-base-content/80">
                    <li>Cancel your subscription immediately.</li>
                    <li>Delete all clients, projects, and time entries.</li>
                    <li>Remove your alert settings and related data.</li>
                </ul>
                <p class="mt-4 text-sm text-base-content/70">To continue, type <span class="font-semibold">DELETE</span> below.</p>

                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 grid gap-4" data-ga-event="delete_account">
                    @csrf
                    @method('DELETE')

                    <label class="form-control">
                        <span class="label-text">Confirmation</span>
                        <input type="text" name="confirmation" class="input input-bordered" placeholder="DELETE" required>
                    </label>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="btn btn-error">Delete my account</button>
                        <a href="{{ route('profile.edit') }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
