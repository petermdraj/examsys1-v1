<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')->whereIn('key', [
            'purchase_receipt',
            'subscription_renewal_reminder',
            'subscription_expired',
            'subscription_grace_ended',
            'payout_paid',
            'payout_rejected',
        ])->delete();
    }

    public function down(): void
    {
        // Templates are restored by EmailTemplateSeeder if needed.
    }
};
