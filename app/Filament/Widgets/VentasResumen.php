<?php

namespace App\Filament\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VentasResumen extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $t = app(SalesReportService::class)->quickTotals();

        $valor = fn (array $r) => '$'.number_format($r['revenue'], 2);
        $desc = fn (array $r) => $r['orders'].' pedidos';

        return [
            Stat::make('Ventas de hoy', $valor($t['hoy']))
                ->description($desc($t['hoy']))
                ->descriptionIcon('heroicon-m-sun')
                ->color('primary'),

            Stat::make('Semana pasada', $valor($t['semana_pasada']))
                ->description($desc($t['semana_pasada']))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Mes pasado', $valor($t['mes_pasado']))
                ->description($desc($t['mes_pasado']))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Año en curso', $valor($t['anio']))
                ->description($desc($t['anio']))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
