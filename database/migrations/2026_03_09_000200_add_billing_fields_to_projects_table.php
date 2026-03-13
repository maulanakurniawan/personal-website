<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('notes');
            $table->boolean('rounding_enabled')->default(false)->after('hourly_rate');
            $table->unsignedInteger('rounding_unit_minutes')->nullable()->after('rounding_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'rounding_enabled', 'rounding_unit_minutes']);
        });
    }
};
