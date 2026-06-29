<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('student_batch_id')->nullable()->after('role');
            $table->foreign('student_batch_id')->references('id')->on('student_batches')->nullOnDelete();
        });

        Schema::create('quiz_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->uuid('lecturer_id');
            $table->uuid('student_batch_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('lecturer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_batch_id')->references('id')->on('student_batches')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['quiz_id', 'student_batch_id'], 'quiz_assignments_quiz_batch_unique');
            $table->unique(['quiz_id', 'user_id'], 'quiz_assignments_quiz_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_assignments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn('student_batch_id');
        });

        Schema::dropIfExists('student_batches');
    }
};
