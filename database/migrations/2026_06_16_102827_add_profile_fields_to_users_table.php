<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 1000)->nullable()->after('avatar');
            $table->string('payout_gateway')->nullable()->after('wallet_balance');
            $table->json('payout_details')->nullable()->after('payout_gateway');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'payout_gateway', 'payout_details']);
        });
    }
};
