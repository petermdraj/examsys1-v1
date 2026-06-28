<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->enum('type', ['mcq_single','mcq_multiple','fill_blank','true_false','short_answer'])->default('mcq_single');
            $table->text('content');
            $table->text('explanation')->nullable();
            $table->decimal('marks', 6, 2)->default(1.00);
            $table->decimal('negative_marks', 6, 2)->default(0.00);
            $table->integer('time_limit_seconds')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_mandatory')->default(true);
            $table->text('hint')->nullable();
            $table->timestamps();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->index(['quiz_id', 'sort_order']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });

        Schema::create('fill_blank_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->string('answer');
            $table->boolean('is_regex')->default(false);
            $table->timestamps();
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('fill_blank_answers');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
