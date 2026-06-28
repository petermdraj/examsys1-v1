<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('platform.subscription_renewal_reminder_days', 7);
        $this->migrator->add('platform.subscription_grace_period_days', 3);
    }

    public function down(): void
    {
        $this->migrator->delete('platform.subscription_renewal_reminder_days');
        $this->migrator->delete('platform.subscription_grace_period_days');
    }
};
