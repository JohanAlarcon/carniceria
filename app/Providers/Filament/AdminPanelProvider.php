<?php

namespace App\Providers\Filament;

use App\Filament\Reports\Widgets\ReportStats;
use App\Filament\Reports\Widgets\SalesByCustomer;
use App\Filament\Reports\Widgets\SalesOverTimeChart;
use App\Filament\Reports\Widgets\TopProducts;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
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
            // Página "Perfil" (menú del avatar) para que el admin cambie su
            // nombre, correo y contraseña. isSimple: false = dentro del panel.
            ->profile(isSimple: false)
            ->brandName('Mi Carnicería')
            ->brandLogo(fn (): HtmlString => new HtmlString(view('filament.brand')->render()))
            ->brandLogoHeight('2.6rem')
            ->favicon(asset('images/app-icon.svg'))
            ->font('Poppins')
            ->colors([
                'primary' => Color::hex('#b91c1c'),
                'gray' => Color::Stone,
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->databaseNotifications()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.head')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => view('filament.login-note')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.orders-realtime')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.pwa')->render(),
            )
            ->navigationGroups([
                'Pedidos',
                'Catálogo',
                'Clientes',
                'Reportes',
                'Configuración',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            // Los widgets de Reportes viven fuera de las carpetas auto-descubiertas
            // (para no aparecer en el Escritorio), así que hay que registrarlos como
            // componentes Livewire; sin esto, sus actualizaciones fallan con 419.
            ->livewireComponents([
                ReportStats::class,
                SalesOverTimeChart::class,
                TopProducts::class,
                SalesByCustomer::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
