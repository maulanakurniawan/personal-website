<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignAdminUser extends Command
{
    protected $signature = 'user:assign-admin {email : Email address for the user to grant admin access}';

    protected $description = 'Assign admin privileges to a user by email';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User with email [{$email}] was not found.");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("User [{$email}] is already an admin.");

            return self::SUCCESS;
        }

        $user->forceFill(['is_admin' => true])->save();

        $this->info("User [{$email}] is now an admin.");

        return self::SUCCESS;
    }
}
