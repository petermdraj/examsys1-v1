<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use App\Settings\PlatformSettings;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class LecturerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('lecturer')
            ->path('lecturer')
            ->login()
            ->registration(\App\Filament\Lecturer\Pages\Auth\Register::class)
            ->colors(function () {
                $primary = rescue(fn () => app(PlatformSettings::class)->primary_color, '#6C2E63', false);
                return [
                    'primary' => Color::hex($primary ?: '#6C2E63'),
                    'danger'  => Color::hex('#D5443F'),
                    'success' => Color::hex('#2E9E68'),
                    'warning' => Color::hex('#E0A431'),
                ];
            })
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('6.5rem')
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => Blade::render('<style>' . file_get_contents(resource_path('css/filament/sidebar.css')) . '</style>'))
            ->renderHook(PanelsRenderHook::TOPBAR_END, function () {
                $show = rescue(fn () => app(PlatformSettings::class)->show_switcher_lecturer, true, false);
                return $show ? view('filament.partials.language-switcher') : '';
            })
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => view('filament.partials.demo-credentials', ['role' => 'lecturer']))
            ->renderHook(PanelsRenderHook::BODY_START, fn () => view('filament.partials.demo-banner'))
            ->discoverResources(in: app_path('Filament/Lecturer/Resources'), for: 'App\\Filament\\Lecturer\\Resources')
            ->discoverPages(in: app_path('Filament/Lecturer/Pages'), for: 'App\\Filament\\Lecturer\\Pages')
            ->pages([
                \App\Filament\Lecturer\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Lecturer/Widgets'), for: 'App\\Filament\\Lecturer\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
