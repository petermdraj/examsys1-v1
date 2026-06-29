<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('hold_results_until_published')->default(false)->after('show_result_immediately');
            $table->timestamp('results_published_at')->nullable()->after('hold_results_until_published');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['hold_results_until_published', 'results_published_at']);
        });
    }
};
