<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Pedidos de los últimos 14 días';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '260px';

    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $labels = [];
        $valores = [];

        foreach (range(13, 0) as $d) {
            $dia = today()->subDays($d);
            $labels[] = $dia->translatedFormat('d M');
            $valores[] = Order::whereDate('created_at', $dia)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $valores,
                    'borderColor' => '#b91c1c',
                    'backgroundColor' => 'rgba(185, 28, 28, 0.12)',
                    'pointBackgroundColor' => '#b91c1c',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
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
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
