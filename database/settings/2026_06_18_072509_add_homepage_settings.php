<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('platform.homepage_theme', 'competition');
        $this->migrator->add('platform.homepage_stat_1_label', '');
        $this->migrator->add('platform.homepage_stat_1_value', '');
        $this->migrator->add('platform.homepage_stat_2_label', '');
        $this->migrator->add('platform.homepage_stat_2_value', '');
        $this->migrator->add('platform.homepage_stat_3_label', '');
        $this->migrator->add('platform.homepage_stat_3_value', '');
    }
};
