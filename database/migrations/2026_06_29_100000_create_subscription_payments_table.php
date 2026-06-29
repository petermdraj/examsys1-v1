<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('plan_id');
            $table->string('billing_cycle', 20);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('gateway', 20);
            $table->string('gateway_order_id')->unique();
            $table->string('gateway_payment_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
