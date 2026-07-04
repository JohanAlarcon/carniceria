<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;

/**
 * Consultas de reportes de ventas.
 *
 * Semántica del negocio (fija):
 * - "Venta" = todos los pedidos MENOS los cancelados.
 * - Fecha base para agrupar por período = orders.created_at.
 * - Monto a nivel pedido = orders.total (incluye impuesto y envío).
 * - Monto a nivel producto = order_items.amount; cantidad = order_items.quantity.
 * - Los productos se agrupan por el nombre guardado (snapshot) en cada línea,
 *   así el reporte no depende de que la variante o el producto sigan existiendo.
 */
class SalesReportService
{
    public const PRESETS = ['hoy', 'semana_pasada', 'mes_pasado', 'anio', 'personalizado'];

    /**
     * Traduce los filtros a un rango de fechas concreto.
     *
     * @param  array  $filters  ['preset' => string, 'desde' => ?string, 'hasta' => ?string]
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function resolveRange(array $filters): array
    {
        $preset = $filters['preset'] ?? 'hoy';

        return match ($preset) {
            'semana_pasada' => [
                'start' => now()->subWeek()->startOfWeek(),
                'end' => now()->subWeek()->endOfWeek(),
                'label' => 'Semana pasada',
            ],
            'mes_pasado' => [
                'start' => now()->startOfMonth()->subMonth(),
                'end' => now()->startOfMonth()->subMonth()->endOfMonth(),
                'label' => 'Mes pasado',
            ],
            'anio' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'label' => 'Año en curso',
            ],
            'personalizado' => $this->customRange($filters),
            default => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'label' => 'Hoy',
            ],
        };
    }

    /** Rango personalizado con fin inclusivo (endOfDay). Sin fechas válidas usa hoy. */
    private function customRange(array $filters): array
    {
        $from = ! empty($filters['desde']) ? Carbon::parse($filters['desde'])->startOfDay() : now()->startOfDay();
        $to = ! empty($filters['hasta']) ? Carbon::parse($filters['hasta'])->endOfDay() : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'start' => $from,
            'end' => $to,
            'label' => $from->format('d/m/Y').' – '.$to->format('d/m/Y'),
        ];
    }

    /** Pedidos que cuentan como venta dentro del rango (excluye cancelados). */
    public function baseSalesQuery(Carbon $start, Carbon $end)
    {
        return Order::query()
            ->where('status', '!=', 'cancelado')
            ->whereBetween('created_at', [$start, $end]);
    }

    /** Líneas de pedido que cuentan como venta (join a orders para excluir cancelados). */
    private function baseItemsQuery(Carbon $start, Carbon $end)
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelado')
            ->whereBetween('orders.created_at', [$start, $end]);
    }

    /**
     * Totales del período.
     *
     * @return array{revenue: float, orders: int, avg_ticket: float, units: float}
     */
    public function summary(Carbon $start, Carbon $end): array
    {
        $orders = (int) $this->baseSalesQuery($start, $end)->count();
        $revenue = round((float) $this->baseSalesQuery($start, $end)->sum('total'), 2);
        $units = round((float) $this->baseItemsQuery($start, $end)->sum('order_items.quantity'), 2);

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'avg_ticket' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
            'units' => $units,
        ];
    }

    /**
     * Serie de ingresos en el tiempo. Diaria si el rango es corto (<= 62 días),
     * mensual si es largo. Rellena con 0 los períodos sin ventas.
     *
     * @return array{labels: string[], data: float[]}
     */
    public function salesOverTime(Carbon $start, Carbon $end): array
    {
        $daily = abs($start->diffInDays($end)) <= 62;
        $sqlFormat = $daily ? '%Y-%m-%d' : '%Y-%m';

        $totals = $this->baseSalesQuery($start, $end)
            ->selectRaw("DATE_FORMAT(created_at, '{$sqlFormat}') as bucket, SUM(total) as revenue")
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket');

        $labels = [];
        $data = [];
        $cursor = $daily ? $start->copy()->startOfDay() : $start->copy()->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->format($daily ? 'Y-m-d' : 'Y-m');
            $labels[] = $daily ? $cursor->translatedFormat('d M') : $cursor->translatedFormat('M Y');
            $data[] = round((float) ($totals[$key] ?? 0), 2);
            $daily ? $cursor->addDay() : $cursor->addMonth();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Productos más vendidos por ingresos (agrupados por nombre snapshot).
     *
     * @return array<int, array{product_name: string, revenue: float, quantity: float}>
     */
    public function topProducts(Carbon $start, Carbon $end, int $limit = 10): array
    {
        return $this->baseItemsQuery($start, $end)
            ->selectRaw('order_items.product_name, SUM(order_items.amount) as revenue, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_name' => (string) $row->product_name,
                'revenue' => round((float) $row->revenue, 2),
                'quantity' => round((float) $row->quantity, 2),
            ])
            ->all();
    }

    /**
     * Ventas por cliente.
     *
     * @return array<int, array{customer_id: int, business_name: string, revenue: float, orders: int, avg_ticket: float}>
     */
    public function salesByCustomer(Carbon $start, Carbon $end): array
    {
        return Order::query()
            ->where('orders.status', '!=', 'cancelado')
            ->whereBetween('orders.created_at', [$start, $end])
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('customers.id as customer_id, customers.business_name, SUM(orders.total) as revenue, COUNT(*) as orders')
            ->groupBy('customers.id', 'customers.business_name')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                $orders = (int) $row->orders;
                $revenue = round((float) $row->revenue, 2);

                return [
                    'customer_id' => (int) $row->customer_id,
                    'business_name' => (string) $row->business_name,
                    'revenue' => $revenue,
                    'orders' => $orders,
                    'avg_ticket' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
                ];
            })
            ->all();
    }

    /**
     * Productos comprados por un cliente en el rango (para el detalle).
     *
     * @return array<int, array{product_name: string, revenue: float, quantity: float}>
     */
    public function productsForCustomer(int $customerId, Carbon $start, Carbon $end): array
    {
        return $this->baseItemsQuery($start, $end)
            ->where('orders.customer_id', $customerId)
            ->selectRaw('order_items.product_name, SUM(order_items.amount) as revenue, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.product_name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'product_name' => (string) $row->product_name,
                'revenue' => round((float) $row->revenue, 2),
                'quantity' => round((float) $row->quantity, 2),
            ])
            ->all();
    }

    /**
     * Resumen rápido para el Escritorio: hoy, semana pasada, mes pasado y año.
     *
     * @return array<string, array{revenue: float, orders: int}>
     */
    public function quickTotals(): array
    {
        $ranges = [
            'hoy' => [now()->startOfDay(), now()->endOfDay()],
            'semana_pasada' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'mes_pasado' => [now()->startOfMonth()->subMonth(), now()->startOfMonth()->subMonth()->endOfMonth()],
            'anio' => [now()->startOfYear(), now()->endOfYear()],
        ];

        $totals = [];
        foreach ($ranges as $key => [$start, $end]) {
            $totals[$key] = [
                'revenue' => round((float) $this->baseSalesQuery($start, $end)->sum('total'), 2),
                'orders' => (int) $this->baseSalesQuery($start, $end)->count(),
            ];
        }

        return $totals;
    }
}
