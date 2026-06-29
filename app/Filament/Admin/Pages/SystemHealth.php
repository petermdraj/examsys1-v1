<?php

namespace App\Filament\Admin\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemHealth extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.admin.pages.system-health';

    public array $appInfo = [];

    public array $permissions = [];

    public array $serverInfo = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_configuration');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_system_health');
    }

    public function getTitle(): string
    {
        return __('admin.nav_system_health');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->loadInfo();
    }

    public function loadInfo(): void
    {
        $dbSize = '—';
        try {
            $dbName = DB::connection()->getDatabaseName();
            $result = DB::select(
                'SELECT round(sum(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.TABLES WHERE table_schema = ?',
                [$dbName]
            );
            $dbSize = ($result[0]->size ?? 0) . ' MB';
        } catch (\Throwable) {
            $dbSize = 'N/A';
        }

        $this->permissions = [
            'storage/framework' => File::isWritable(storage_path('framework')),
            'storage/logs'      => File::isWritable(storage_path('logs')),
            'bootstrap/cache'   => File::isWritable(base_path('bootstrap/cache')),
            '.env'              => File::isWritable(base_path('.env')),
        ];

        $this->appInfo = [
            'name'    => config('app.name'),
            'env'     => config('app.env'),
            'debug'   => config('app.debug') ? 'On' : 'Off',
            'url'     => config('app.url'),
            'laravel' => app()->version(),
            'php'     => PHP_VERSION,
            'db'      => config('database.default'),
            'db_size' => $dbSize,
        ];

        $this->serverInfo = [
            'memory_limit'  => ini_get('memory_limit'),
            'upload_max'    => ini_get('upload_max_filesize'),
            'post_max'      => ini_get('post_max_size'),
            'max_execution' => ini_get('max_execution_time') . 's',
        ];
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        Notification::make()
            ->title(__('admin.system_cache_cleared'))
            ->success()
            ->send();
    }
}
