<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('platform.certificate_logo', null);
    }

    public function down(): void
    {
        $this->migrator->delete('platform.certificate_logo');
    }
};
