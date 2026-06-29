<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quiz_enrollments') || ! Schema::hasColumn('quiz_enrollments', 'source')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quiz_enrollments MODIFY COLUMN source ENUM('free','purchased','gifted','admin','assigned') NOT NULL DEFAULT 'free'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('quiz_enrollments') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('quiz_enrollments')->where('source', 'assigned')->update(['source' => 'free']);
        DB::statement("ALTER TABLE quiz_enrollments MODIFY COLUMN source ENUM('free','purchased','gifted','admin') NOT NULL DEFAULT 'free'");
    }
};
