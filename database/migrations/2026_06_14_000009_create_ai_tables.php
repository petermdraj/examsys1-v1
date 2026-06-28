<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('ai_generation_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('quiz_id')->nullable();
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->integer('questions_generated')->default(0);
            $table->integer('tokens_used')->default(0);
            $table->decimal('charge_applied', 6, 4)->default(0);
            $table->boolean('was_free')->default(false);
            $table->string('model')->default('gpt-4o');
            $table->enum('status', ['success','failed','partial'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('ai_credit_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('type', ['free_allocation','purchased','consumed','refunded','admin_grant']);
            $table->integer('amount');
            $table->integer('balance_after');
            $table->string('description');
            $table->uuid('reference_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ai_credit_transactions');
        Schema::dropIfExists('ai_generation_logs');
    }
};
