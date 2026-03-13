<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });


        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->text('task')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->boolean('is_billable')->default(true);
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index(['user_id', 'invoiced_at']);
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
    }
};
