<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->delete('platform.allow_registration');
        $this->migrator->delete('platform.lecturer_registration_open');
    }

    public function down(): void
    {
        $this->migrator->add('platform.allow_registration', false);
        $this->migrator->add('platform.lecturer_registration_open', false);
    }
};
