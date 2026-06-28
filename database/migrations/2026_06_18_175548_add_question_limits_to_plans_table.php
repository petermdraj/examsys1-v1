<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_questions_per_quiz')->nullable()->after('max_published_quizzes')
                ->comment('Max questions in a single quiz (null = unlimited)');
            $table->unsignedInteger('max_questions_in_bank')->nullable()->after('max_questions_per_quiz')
                ->comment('Max total questions across all quizzes (null = unlimited)');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_questions_per_quiz', 'max_questions_in_bank']);
        });
    }
};
