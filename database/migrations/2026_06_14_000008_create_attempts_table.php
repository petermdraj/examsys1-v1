<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->uuid('user_id');
            $table->uuid('enrollment_id');
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['in_progress','completed','abandoned','timed_out'])->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('time_taken_seconds')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_passed')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('enrollment_id')->references('id')->on('quiz_enrollments')->cascadeOnDelete();
            $table->index(['user_id', 'quiz_id', 'status']);
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id');
            $table->uuid('question_id');
            $table->json('selected_options')->nullable();
            $table->string('text_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('marks_earned', 6, 2)->default(0);
            $table->boolean('is_marked_for_review')->default(false);
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->foreign('attempt_id')->references('id')->on('attempts')->cascadeOnDelete();
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
            $table->index(['attempt_id', 'question_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('attempts');
    }
};
