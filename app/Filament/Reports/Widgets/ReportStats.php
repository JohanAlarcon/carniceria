<?php

namespace App\Filament\Reports\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $service = app(SalesReportService::class);
        $range = $service->resolveRange($this->filters ?? []);
        $summary = $service->summary($range['start'], $range['end']);
        $trend = $service->salesOverTime($range['start'], $range['end'])['data'];

        return [
            Stat::make('Ingresos', '$'.number_format($summary['revenue'], 2))
                ->description($range['label'].' · no cancelados')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($trend),

            Stat::make('Pedidos', $summary['orders'])
                ->description('En el período')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Ticket promedio', '$'.number_format($summary['avg_ticket'], 2))
                ->description('Ventas ÷ N° de pedidos')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Unidades vendidas', number_format($summary['units'], 2))
                ->description('Suma de cantidades')
                ->descriptionIcon('heroicon-m-scale')
                ->color('warning'),
        ];
    }
}
