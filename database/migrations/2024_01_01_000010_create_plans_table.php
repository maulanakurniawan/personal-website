<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('internal_code')->unique();
            $table->string('paddle_product_id')->nullable();
            $table->string('paddle_price_id')->unique()->nullable();
            $table->string('name');
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('billing_interval')->default('month');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
