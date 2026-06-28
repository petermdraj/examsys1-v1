<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop existing CASCADE FK, make quiz_id nullable, add ON DELETE SET NULL
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->uuid('quiz_id')->nullable()->change();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->nullOnDelete();
        });

        // Pivot: assign questions from the bank to quizzes
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->uuid('quiz_id');
            $table->uuid('question_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->primary(['quiz_id', 'question_id']);
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
            $table->index(['quiz_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->uuid('quiz_id')->nullable(false)->change();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
        });
    }
};
