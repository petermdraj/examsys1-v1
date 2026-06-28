<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('certificate_logo')->nullable()->after('avatar');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('allow_custom_certificate_logo')->default(false)->after('can_sell_paid_quizzes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('certificate_logo');
        });
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('allow_custom_certificate_logo');
        });
    }
};
