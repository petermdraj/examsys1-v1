<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('quiz_id');
            $table->decimal('amount', 8, 2);
            $table->char('currency', 3)->default('USD');
            $table->decimal('platform_commission', 8, 2)->default(0);
            $table->decimal('creator_earning', 8, 2)->default(0);
            $table->enum('status', ['pending','paid','failed','refunded'])->default('pending');
            $table->enum('gateway', ['stripe','razorpay','paypal','wallet'])->default('razorpay');
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('settled_at')->nullable(); // set by CommissionService::settle() for idempotency
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->index(['user_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
