<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['group' => 'platform', 'name' => 'extra_locale_flags', 'payload' => json_encode([])],
            ['group' => 'platform', 'name' => 'rtl_locales',        'payload' => json_encode([])],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['group' => $row['group'], 'name' => $row['name']],
                ['payload' => $row['payload']]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'platform')
            ->whereIn('name', ['extra_locale_flags', 'rtl_locales'])
            ->delete();
    }
};
