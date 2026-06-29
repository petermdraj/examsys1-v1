<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id')->unique();
            $table->uuid('user_id');
            $table->uuid('quiz_id');
            $table->uuid('uuid')->unique();
            $table->timestamp('issued_at')->useCurrent();
            $table->string('pdf_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('attempt_id')->references('id')->on('attempts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
        });

        Schema::create('creator_payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lecturer_id');
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['pending','processing','paid','failed'])->default('pending');
            $table->string('gateway')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->foreign('lecturer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('creator_payouts');
        Schema::dropIfExists('certificates');
    }
};
