<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            // Covers: WHERE quiz_id = ? AND status = 'completed' ORDER BY percentage DESC, time_taken_seconds ASC
            $table->index(['quiz_id', 'status', 'percentage', 'time_taken_seconds'], 'attempts_leaderboard_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex('attempts_leaderboard_idx');
        });
    }
};
