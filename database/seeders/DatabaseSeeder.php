<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'email' => 'demo@solohours.com',
            'plan' => User::PLAN_STARTER,
        ]);
    }
}
