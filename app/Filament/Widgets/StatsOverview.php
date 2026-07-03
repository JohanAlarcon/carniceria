<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $pendientes = Order::where('status', 'pendiente')->count();

        $ventasMes = (float) Order::whereNotIn('status', ['cancelado'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $pedidosHoy = Order::whereDate('created_at', today())->count();
        $porAprobar = Customer::where('is_approved', false)->count();
        $publicados = Product::where('is_published', true)->count();

        // Mini-tendencia: pedidos por día en los últimos 7 días.
        $tendencia = collect(range(6, 0))
            ->map(fn (int $d) => Order::whereDate('created_at', today()->subDays($d))->count())
            ->all();

        return [
            Stat::make('Pedidos pendientes', $pendientes)
                ->description($pendientes > 0 ? 'Requieren atención' : 'Todo al día')
                ->descriptionIcon($pendientes > 0 ? 'heroicon-m-bell-alert' : 'heroicon-m-check-circle')
                ->icon('heroicon-o-clipboard-document-list')
                ->color($pendientes > 0 ? 'warning' : 'success')
                ->chart($tendencia),

            Stat::make('Ventas del mes', '$'.number_format($ventasMes, 2))
                ->description('Pedidos no cancelados')
                ->descriptionIcon('heroicon-m-banknotes')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart($tendencia),

            Stat::make('Pedidos hoy', $pedidosHoy)
                ->description('Recibidos en el día')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->chart($tendencia),

            Stat::make('Clientes por aprobar', $porAprobar)
                ->description($porAprobar > 0 ? 'Pendientes de revisión' : 'Sin pendientes')
                ->descriptionIcon('heroicon-m-user-plus')
                ->icon('heroicon-o-users')
                ->color($porAprobar > 0 ? 'danger' : 'gray'),
        ];
    }
}
