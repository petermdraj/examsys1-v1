<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['license_key', 'license_domain', 'license_status'] as $key) {
            $this->migrator->delete("platform.{$key}");
        }
    }

    public function down(): void
    {
        $this->migrator->add('platform.license_key', '');
        $this->migrator->add('platform.license_domain', '');
        $this->migrator->add('platform.license_status', '');
    }
};
