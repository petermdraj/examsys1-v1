<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['group' => 'platform', 'name' => 'enabled_locales',       'payload' => json_encode(['en'])],
            ['group' => 'platform', 'name' => 'show_switcher_admin',   'payload' => json_encode(true)],
            ['group' => 'platform', 'name' => 'show_switcher_creator', 'payload' => json_encode(true)],
            ['group' => 'platform', 'name' => 'show_switcher_front',   'payload' => json_encode(true)],
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
            ->whereIn('name', ['enabled_locales', 'show_switcher_admin', 'show_switcher_creator', 'show_switcher_front'])
            ->delete();
    }
};
