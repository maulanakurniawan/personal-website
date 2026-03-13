<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('time_entries')) {
            return;
        }

        if (Schema::hasColumn('time_entries', 'description') && ! Schema::hasColumn('time_entries', 'task')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->renameColumn('description', 'task');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('time_entries')) {
            return;
        }

        if (Schema::hasColumn('time_entries', 'task') && ! Schema::hasColumn('time_entries', 'description')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->renameColumn('task', 'description');
            });
        }
    }
};
