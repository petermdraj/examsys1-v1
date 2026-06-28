<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The spatie/laravel-permission tables were created with unsignedBigInteger
    // for the model morph key, but User PKs are UUID (char 36). Fix the type.
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE model_has_roles MODIFY model_uuid CHAR(36) NOT NULL');
            DB::statement('ALTER TABLE model_has_permissions MODIFY model_uuid CHAR(36) NOT NULL');
        } else {
            // SQLite / testing: recreate columns via Schema Builder
            foreach (['model_has_roles', 'model_has_permissions'] as $table) {
                Schema::table($table, fn (Blueprint $t) => $t->string('model_uuid', 36)->nullable(false)->change());
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE model_has_roles MODIFY model_uuid BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE model_has_permissions MODIFY model_uuid BIGINT UNSIGNED NOT NULL');
        }
    }
};
