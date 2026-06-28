<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear sessions first (they're all invalid anyway)
        DB::table('sessions')->truncate();

        Schema::table('sessions', function ($table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function ($table) {
            $table->uuid('user_id')->nullable()->index()->after('id');
        });
    }

    public function down(): void
    {
        DB::table('sessions')->truncate();

        Schema::table('sessions', function ($table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function ($table) {
            $table->foreignId('user_id')->nullable()->index()->after('id');
        });
    }
};
