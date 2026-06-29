<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->delete('platform.footer_show_newsletter');
        } catch (\Throwable) {
            // Key was never added on fresh installs — safe to ignore.
        }
    }

    public function down(): void
    {
        $this->migrator->add('platform.footer_show_newsletter', true);
    }
};
