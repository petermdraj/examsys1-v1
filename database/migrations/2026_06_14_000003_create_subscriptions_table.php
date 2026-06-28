<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('plan_id');
            $table->enum('status', ['active','cancelled','expired','trialing'])->default('active');
            $table->enum('billing_cycle', ['monthly','yearly'])->default('monthly');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->enum('gateway', ['stripe','razorpay','manual'])->default('manual');
            $table->string('gateway_subscription_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
