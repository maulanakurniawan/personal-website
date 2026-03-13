<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paddle_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paddle_transaction_id')->nullable()->index();
            $table->string('paddle_subscription_id')->nullable()->index();
            $table->string('paddle_adjustment_id')->nullable()->index();
            $table->string('event_type');
            $table->string('status')->nullable();
            $table->string('currency', 3)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->string('adjustment_action')->nullable();
            $table->string('adjustment_status')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->json('details')->nullable();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paddle_transactions');
    }
};
