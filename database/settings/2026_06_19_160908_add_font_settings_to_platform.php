<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('platform.font_primary',   'Inter');
        $this->migrator->add('platform.font_display',   'Plus Jakarta Sans');
        $this->migrator->add('platform.font_size_base', '16px');
    }
};
