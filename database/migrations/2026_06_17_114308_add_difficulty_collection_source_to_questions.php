<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable()->after('hint');
            $table->foreignUuid('collection_id')->nullable()->after('difficulty')
                ->constrained('question_collections')->nullOnDelete();
            $table->foreignUuid('source_bank_question_id')->nullable()->after('collection_id')
                ->constrained('questions')->nullOnDelete();
            // composite index for bank queries: WHERE creator_id=? AND quiz_id IS NULL
            $table->index(['creator_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['source_bank_question_id']);
            $table->dropForeign(['collection_id']);
            $table->dropIndex(['creator_id', 'quiz_id']);
            $table->dropColumn(['difficulty', 'collection_id', 'source_bank_question_id']);
        });
    }
};
