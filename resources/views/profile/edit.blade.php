<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body">
                <h1 class="text-xl font-semibold">Profile</h1>
                <p class="text-sm text-base-content/70">Update your name and email address.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-4 grid gap-4">
                    @csrf
                    @method('PATCH')

                    <label class="form-control">
                        <span class="label-text">Name</span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input input-bordered" required>
                    </label>

                    <label class="form-control">
                        <span class="label-text">Email</span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input input-bordered" required>
                    </label>

                    <div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>


        <div class="card bg-base-100 border border-error/40 shadow-sm">
            <div class="card-body">
                <h2 class="text-lg font-semibold text-error">Delete account</h2>
                <p class="text-sm text-base-content/70">Deleting your account is permanent. Your subscription will be canceled immediately, and all projects, clients, and time entries will be removed.</p>
                <div class="mt-4">
                    <a href="{{ route('profile.delete.confirm') }}" class="btn btn-error">Delete account</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
