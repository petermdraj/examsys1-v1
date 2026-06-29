<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','lecturer','student') NOT NULL DEFAULT 'student'");
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'admin')->update(['role' => 'lecturer']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','lecturer','student') NOT NULL DEFAULT 'student'");
        }
    }
};
