<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('product_key')->index();
            $table->string('product_name');
            $table->string('key_prefix')->nullable()->index();
            $table->string('key_hash')->unique();
            $table->json('allowed_hosts')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_api_clients');
    }
};
