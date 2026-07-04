<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_leads', function (Blueprint $table) {
            $table->id();
            $table->string('product_key')->index();
            $table->string('product_name')->nullable();
            $table->text('source_url')->nullable();
            $table->string('email')->index();
            $table->string('locale', 10)->nullable();
            $table->string('target_category', 100)->nullable();
            $table->string('price_interest', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('price_seen_currency', 10)->nullable();
            $table->decimal('price_seen_amount', 10, 2)->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('ip_hash', 128)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status')->default('new')->index();
            $table->unsignedInteger('submission_count')->default(1);
            $table->timestamp('last_submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['product_key', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_leads');
    }
};
