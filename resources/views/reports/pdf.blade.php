<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { color: #2a211d; font-size: 12px; margin: 0; }
        .header { background: #b91c1c; color: #fff; padding: 18px 24px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header .sub { font-size: 11px; margin-top: 4px; }
        .container { padding: 20px 24px; }
        table.cards { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.cards td { border: 1px solid #e7dbc9; padding: 10px 12px; width: 25%; }
        .cards .label { font-size: 9px; color: #9a8778; text-transform: uppercase; }
        .cards .value { font-size: 15px; font-weight: bold; }
        h2 { font-size: 13px; color: #b91c1c; border-bottom: 2px solid #b91c1c; padding-bottom: 4px; margin: 20px 0 8px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f2e9dc; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #7a6656; }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .muted { color: #9a8778; }
        .foot { margin-top: 24px; font-size: 9px; color: #9a8778; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $business->business_name ?? 'Mi Carnicería' }}</h1>
        <div class="sub">Reporte de ventas · {{ $range['label'] }}</div>
    </div>

    <div class="container">
        <table class="cards">
            <tr>
                <td><div class="label">Ingresos</div><div class="value">${{ number_format($summary['revenue'], 2) }}</div></td>
                <td><div class="label">Pedidos</div><div class="value">{{ $summary['orders'] }}</div></td>
                <td><div class="label">Ticket promedio</div><div class="value">${{ number_format($summary['avg_ticket'], 2) }}</div></td>
                <td><div class="label">Unidades vendidas</div><div class="value">{{ number_format($summary['units'], 2) }}</div></td>
            </tr>
        </table>

        <h2>Productos más vendidos</h2>
        <table class="data">
            <thead>
                <tr>
                    <th style="width:8%">#</th>
                    <th>Producto</th>
                    <th class="right" style="width:22%">Ingresos</th>
                    <th class="right" style="width:20%">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topProducts as $i => $p)
                    <tr>
                        <td class="muted">{{ $i + 1 }}</td>
                        <td>{{ $p['product_name'] }}</td>
                        <td class="right">${{ number_format($p['revenue'], 2) }}</td>
                        <td class="right">{{ number_format($p['quantity'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Sin ventas en el período.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Ventas por cliente</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="right" style="width:22%">Ingresos</th>
                    <th class="right" style="width:14%">Pedidos</th>
                    <th class="right" style="width:20%">Ticket prom.</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($byCustomer as $c)
                    <tr>
                        <td>{{ $c['business_name'] }}</td>
                        <td class="right">${{ number_format($c['revenue'], 2) }}</td>
                        <td class="right">{{ $c['orders'] }}</td>
                        <td class="right">${{ number_format($c['avg_ticket'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Sin ventas en el período.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Ventas en el tiempo</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Período</th>
                    <th class="right" style="width:30%">Ventas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($series['labels'] as $i => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="right">${{ number_format($series['data'][$i] ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">Sin datos en el período.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="foot">Generado el {{ $generatedAt }}</div>
    </div>
</body>
</html>
