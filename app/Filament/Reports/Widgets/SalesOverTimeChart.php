<?php

namespace App\Filament\Reports\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesOverTimeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Ventas en el tiempo';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $service = app(SalesReportService::class);
        $range = $service->resolveRange($this->filters ?? []);
        $series = $service->salesOverTime($range['start'], $range['end']);

        return [
            'datasets' => [
                [
                    'label' => 'Ventas ($)',
                    'data' => $series['data'],
                    'borderColor' => '#b91c1c',
                    'backgroundColor' => 'rgba(185, 28, 28, 0.12)',
                    'pointBackgroundColor' => '#b91c1c',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
