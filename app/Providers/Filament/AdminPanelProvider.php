<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use App\Settings\PlatformSettings;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Platform;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(PanelsRenderHook::TOPBAR_END, function () {
                $show = rescue(fn () => app(PlatformSettings::class)->show_switcher_admin, true, false);
                return $show ? view('filament.partials.language-switcher') : '';
            })
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => view('filament.partials.demo-credentials', ['role' => 'admin']))
            ->renderHook(PanelsRenderHook::BODY_START, fn () => view('filament.partials.demo-banner'))
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make(__('admin.nav_group_platform'))->collapsible(false),
                \Filament\Navigation\NavigationGroup::make(__('admin.nav_group_content'))->collapsible(false),
                \Filament\Navigation\NavigationGroup::make(__('admin.nav_group_configuration'))->collapsible(false),
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
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
