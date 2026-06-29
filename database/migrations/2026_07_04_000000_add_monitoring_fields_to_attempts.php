<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->string('risk_level')->default('low')->after('user_agent');
            $table->unsignedSmallInteger('risk_score')->default(0)->after('risk_level');
            $table->json('flagged_events')->nullable()->after('risk_score');
            $table->timestamp('last_activity_at')->nullable()->after('flagged_events');
            $table->unsignedSmallInteger('tab_switch_count')->default(0)->after('last_activity_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attempts MODIFY COLUMN status ENUM('in_progress','completed','abandoned','timed_out','paused','terminated') NOT NULL DEFAULT 'in_progress'");
        } else {
            Schema::table('attempts', function (Blueprint $table) {
                $table->string('status')->default('in_progress')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn([
                'risk_level',
                'risk_score',
                'flagged_events',
                'last_activity_at',
                'tab_switch_count',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attempts MODIFY COLUMN status ENUM('in_progress','completed','abandoned','timed_out') NOT NULL DEFAULT 'in_progress'");
        }
    }
};
