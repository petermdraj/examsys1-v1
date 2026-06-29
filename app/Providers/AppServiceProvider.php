<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\AI\QuizGeneratorService::class, function ($app) {
            return new \App\Services\AI\QuizGeneratorService(
                $app->make(\App\Services\AI\QuizGeneratorPromptBuilder::class),
                $app->make(\App\Services\AI\QuizGeneratorResponseParser::class),
            );
        });
    }

    public function boot(): void
    {
        // ── Multi-instance subfolder URL fix ──────────────────────────────────────
        // When running as /competition, /school, /professional — force Laravel's
        // URL generator to prefix all generated URLs with the instance path so
        // links and redirects stay within the correct subfolder.
        if ($instance = $_SERVER['EXAMSYS_INSTANCE'] ?? null) {
            \Illuminate\Support\Facades\URL::forceRootUrl(
                rtrim(config('app.url'), '/')
            );
        }
        // ─────────────────────────────────────────────────────────────────────────

        // Before installation the sessions table doesn't exist and Livewire AJAX
        // requests go to /livewire/update (not /install*), so we must force the
        // file driver for the entire pre-install lifecycle — not just page loads.
        if (!file_exists(storage_path('installed'))) {
            // Force file-based session + cache during the entire installer
            // lifecycle so that writing CACHE_STORE=database to .env (step 1)
            // and clearing config cache (step 2) never break subsequent
            // wire:poll requests that fire before migrations have run (step 3).
            \Illuminate\Support\Facades\Config::set('session.driver', 'file');
            \Illuminate\Support\Facades\Config::set('cache.default', 'file');
        }

        \Illuminate\Pagination\Paginator::useTailwind();

        // ── Demo Mode: intercept destructive Filament actions globally ────────────
        // When DEMO_MODE=true, delete actions show a notification and cancel
        // instead of removing records. Edit-page saves are blocked via the
        // DemoModeEditPage trait on each EditRecord page.
        if (config('examsys.demo_mode')) {
            // Table row → Delete
            \Filament\Tables\Actions\DeleteAction::configureUsing(
                fn (\Filament\Tables\Actions\DeleteAction $action) =>
                    $action->before(function (\Filament\Tables\Actions\DeleteAction $action) {
                        \Filament\Notifications\Notification::make()
                            ->title('Demo Mode — Deletion disabled')
                            ->body('This is a read-only demo. Records cannot be deleted.')
                            ->danger()
                            ->persistent()
                            ->send();
                        $action->cancel();
                    })
            );

            // Table → Bulk delete
            \Filament\Tables\Actions\DeleteBulkAction::configureUsing(
                fn (\Filament\Tables\Actions\DeleteBulkAction $action) =>
                    $action->before(function (\Filament\Tables\Actions\DeleteBulkAction $action) {
                        \Filament\Notifications\Notification::make()
                            ->title('Demo Mode — Deletion disabled')
                            ->body('This is a read-only demo. Records cannot be deleted.')
                            ->danger()
                            ->persistent()
                            ->send();
                        $action->cancel();
                    })
            );

            // Edit-page header → Delete (Filament\Actions not Filament\Tables\Actions)
            \Filament\Actions\DeleteAction::configureUsing(
                fn (\Filament\Actions\DeleteAction $action) =>
                    $action->before(function (\Filament\Actions\DeleteAction $action) {
                        \Filament\Notifications\Notification::make()
                            ->title('Demo Mode — Deletion disabled')
                            ->body('This is a read-only demo. Records cannot be deleted.')
                            ->danger()
                            ->persistent()
                            ->send();
                        $action->halt();
                    })
            );
        }
        // ─────────────────────────────────────────────────────────────────────────

        // Apply DB-stored settings to runtime config on every request boot.
        // Wrapped in try/catch so a missing settings table (fresh install) doesn't
        // crash the app before migrations have run.
        try {
            // Guard against pre-migration state (installer, fresh deploy).
            // Schema::hasTable() is safe to call even without a connection — it
            // returns false rather than throwing when the table is missing.
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // Pre-migration state (installer, fresh test run via RefreshDatabase).
                // Share defaults so Blade views that reference $platformSettings don't
                // throw "Undefined variable" — the in-memory defaults are safe with no DB.
                $defaults = new \App\Settings\PlatformSettings();
                \Illuminate\Support\Facades\View::share('platformSettings', $defaults);
                \Illuminate\Support\Facades\View::share('sym', $defaults->currency_symbol);
                \Illuminate\Support\Facades\View::share('currencyCode', $defaults->default_currency);
                return;
            }

            $s = app(\App\Settings\PlatformSettings::class);

            // Share settings with all Blade views
            \Illuminate\Support\Facades\View::share('platformSettings', $s);
            \Illuminate\Support\Facades\View::share('sym', $s->currency_symbol);
            \Illuminate\Support\Facades\View::share('currencyCode', $s->default_currency);

            // Sync app.name from DB setting
            \Illuminate\Support\Facades\Config::set('app.name', $s->app_name);

            if (! empty($s->openai_api_key)) {
                \Illuminate\Support\Facades\Config::set('openai.api_key', $s->openai_api_key);
            }
            if (! empty($s->openai_organization)) {
                \Illuminate\Support\Facades\Config::set('openai.organization', $s->openai_organization);
            }

            // Google OAuth — populated from Settings → Registration tab
            if (! empty($s->google_client_id)) {
                \Illuminate\Support\Facades\Config::set([
                    'services.google.client_id'     => $s->google_client_id,
                    'services.google.client_secret' => $s->google_client_secret,
                    'services.google.redirect'      => url('/auth/google/callback'),
                ]);
            }
            if (! empty($s->mail_host)) {
                \Illuminate\Support\Facades\Config::set([
                    'mail.default'                 => 'smtp',
                    'mail.mailers.smtp.host'       => $s->mail_host,
                    'mail.mailers.smtp.port'       => (int) $s->mail_port,
                    'mail.mailers.smtp.encryption' => $s->mail_encryption ?: null,
                    'mail.mailers.smtp.username'   => $s->mail_username,
                    'mail.mailers.smtp.password'   => $s->mail_password,
                    'mail.from.address'            => $s->mail_from_address,
                    'mail.from.name'               => $s->mail_from_name,
                ]);
            }

            if (! empty($s->filesystem_disk) && $s->filesystem_disk === 's3'
                && class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class)) {
                \Illuminate\Support\Facades\Config::set([
                    'filesystems.default'           => 's3',
                    'filesystems.disks.s3.key'      => $s->aws_key,
                    'filesystems.disks.s3.secret'   => $s->aws_secret,
                    'filesystems.disks.s3.region'   => $s->aws_region,
                    'filesystems.disks.s3.bucket'   => $s->aws_bucket,
                    'filesystems.disks.s3.url'      => $s->aws_url ?: null,
                ]);
            }
        } catch (\Throwable) {
            // Settings table not yet available (pre-migration) — use .env defaults.
        }
    }
}
