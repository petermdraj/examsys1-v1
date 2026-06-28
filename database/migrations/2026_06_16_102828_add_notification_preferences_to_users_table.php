<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('payout_details');
        });

        // Backfill existing rows with the default preferences so they aren't null.
        DB::table('users')
            ->whereNull('notification_preferences')
            ->update(['notification_preferences' => json_encode([
                'notify_quiz_results'  => true,
                'notify_purchases'     => true,
                'notify_weekly_digest' => false,
            ])]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
