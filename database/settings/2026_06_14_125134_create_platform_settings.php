<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('platform.app_name', 'Quizora');
        $this->migrator->add('platform.app_logo', '');
        $this->migrator->add('platform.ai_free_generations_default', 10);
        $this->migrator->add('platform.ai_charge_per_gen_default', 5.00);
        $this->migrator->add('platform.platform_commission_default', 20.0);
        $this->migrator->add('platform.razorpay_key', '');
        $this->migrator->add('platform.razorpay_secret', '');
        $this->migrator->add('platform.stripe_key', '');
        $this->migrator->add('platform.stripe_secret', '');
        $this->migrator->add('platform.stripe_webhook_secret', '');
        $this->migrator->add('platform.mail_host', '');
        $this->migrator->add('platform.mail_port', '587');
        $this->migrator->add('platform.mail_username', '');
        $this->migrator->add('platform.mail_password', '');
        $this->migrator->add('platform.mail_from_address', '');
        $this->migrator->add('platform.mail_from_name', '');
    }
};
