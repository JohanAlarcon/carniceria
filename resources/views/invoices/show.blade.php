<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $order->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; margin: 0; padding: 28px 32px; }
        .row { width: 100%; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .logo { width: 64px; height: 64px; }
        .brand { font-size: 18px; font-weight: bold; }
        .muted { color: #6b7280; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 34px; color: #b91c1c; letter-spacing: 1px; }
        .title .invno { font-weight: bold; font-size: 13px; }
        .balance-box { margin-top: 10px; text-align: right; }
        .balance-box .lbl { font-size: 11px; color: #6b7280; }
        .balance-box .amt { font-size: 18px; font-weight: bold; }
        .biz { margin-top: 18px; }
        .biz .name { font-weight: bold; font-size: 13px; }
        .meta { margin-top: 8px; }
        .meta td { padding: 2px 0; }
        .meta .k { color: #6b7280; text-align: right; padding-right: 8px; }
        .meta .v { text-align: right; width: 130px; }
        .billto { margin-top: 6px; }
        .billto .lbl { color: #6b7280; }
        .billto .who { font-weight: bold; }
        .items { margin-top: 20px; }
        .items thead td { background: #b91c1c; color: #fff; padding: 8px 10px; font-weight: bold; font-size: 11px; }
        .items tbody td { padding: 9px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .items .num { width: 30px; }
        .items .qty, .items .rate, .items .amount { text-align: right; white-space: nowrap; }
        .items .unit { color: #6b7280; font-size: 10px; }
        .totals { margin-top: 12px; }
        .totals td { padding: 6px 10px; }
        .totals .k { text-align: right; color: #374151; white-space: nowrap; }
        .totals .v { text-align: right; width: 130px; white-space: nowrap; }
        .totals .grand td { border-top: 2px solid #111827; font-weight: bold; font-size: 14px; }
        .totals .due td { background: #f3f4f6; font-weight: bold; }
        .notes { margin-top: 26px; }
        .notes h4 { margin: 0 0 4px; font-size: 12px; }
        .notes p, .terms p { margin: 0 0 6px; color: #374151; white-space: pre-line; }
        .terms { margin-top: 16px; font-size: 10px; }
    </style>
</head>
<body>
    @php
        $addr = trim(collect([$settings->city, $settings->state, $settings->zip])->filter()->implode(', '));
        $itemsCount = $order->items->count();
    @endphp

    <table class="header">
        <tr>
            <td style="width: 60%;">
                @if ($settings->logo_path && file_exists(public_path('storage/'.$settings->logo_path)))
                    <img class="logo" src="{{ public_path('storage/'.$settings->logo_path) }}" alt="logo">
                @endif
                <div class="brand">{{ $settings->business_name }}</div>
                <div class="biz muted">
                    @if ($settings->address_line1){{ $settings->address_line1 }}<br>@endif
                    @if ($settings->address_line2){{ $settings->address_line2 }}<br>@endif
                    {{ $addr }}<br>
                    {{ $settings->country }}<br>
                    @if ($settings->phone){{ $settings->phone }}<br>@endif
                    @if ($settings->email){{ $settings->email }}@endif
                </div>
            </td>
            <td class="title" style="width: 40%;">
                <h1>INVOICE</h1>
                <div class="invno">Factura# {{ $order->invoice_number }}</div>
                <div class="balance-box">
                    <div class="lbl">Balance Due / Saldo</div>
                    <div class="amt">${{ number_format((float) $order->total, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table style="margin-top: 18px;">
        <tr>
            <td style="width: 55%; vertical-align: bottom;">
                <div class="billto">
                    <div class="lbl">Bill To / Cliente</div>
                    <div class="who">{{ $order->delivery_business_name ?: $order->customer?->business_name }}</div>
                    @if ($order->delivery_contact_name)<div>{{ $order->delivery_contact_name }}</div>@endif
                    @php
                        $daddr = collect([
                            $order->delivery_address_line1,
                            $order->delivery_address_line2,
                            trim(collect([$order->delivery_city, $order->delivery_state, $order->delivery_zip])->filter()->implode(', ')),
                        ])->filter();
                    @endphp
                    @foreach ($daddr as $line)<div class="muted">{{ $line }}</div>@endforeach
                    @if ($order->delivery_phone)<div class="muted">{{ $order->delivery_phone }}</div>@endif
                </div>
            </td>
            <td style="width: 45%;">
                <table class="meta">
                    <tr><td class="k">Invoice Date / Fecha :</td><td class="v">{{ optional($order->invoiced_at ?? $order->placed_at ?? $order->created_at)->format('d M Y') }}</td></tr>
                    <tr><td class="k">Terms / Términos :</td><td class="v">Due on Receipt</td></tr>
                    @if ($order->requested_date)
                    <tr><td class="k">Entrega :</td><td class="v">{{ $order->requested_date->format('d M Y') }}</td></tr>
                    @endif
                    <tr><td class="k">Pedido :</td><td class="v">{{ $order->order_number }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <td class="num">#</td>
                <td>Item &amp; Description</td>
                <td class="qty">Qty</td>
                <td class="rate">Rate</td>
                <td class="amount">Amount</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $i => $item)
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if ($item->variant_label)<div class="unit">{{ $item->variant_label }}</div>@endif
                    </td>
                    <td class="qty">
                        {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                        <div class="unit">{{ $item->unit_label }}</div>
                    </td>
                    <td class="rate">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="amount">{{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td style="width: 60%;"></td>
            <td class="k" style="width: 25%;">Sub Total</td>
            <td class="v">{{ number_format((float) $order->subtotal, 2) }}</td>
        </tr>
        @if ((float) $order->shipping_fee > 0)
        <tr>
            <td></td>
            <td class="k">Shipping / Flete</td>
            <td class="v">{{ number_format((float) $order->shipping_fee, 2) }}</td>
        </tr>
        @endif
        @if ((float) $order->tax > 0)
        <tr>
            <td></td>
            <td class="k">Tax</td>
            <td class="v">{{ number_format((float) $order->tax, 2) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td></td>
            <td class="k">Total</td>
            <td class="v">${{ number_format((float) $order->total, 2) }}</td>
        </tr>
        <tr class="due">
            <td></td>
            <td class="k">Balance Due</td>
            <td class="v">${{ number_format((float) $order->total, 2) }}</td>
        </tr>
    </table>

    @if ($settings->invoice_notes_es || $settings->invoice_notes_en)
        <div class="notes">
            <h4>Notes / Notas</h4>
            <p>{{ $settings->invoice_notes_es }}@if ($settings->invoice_notes_en) / {{ $settings->invoice_notes_en }}@endif</p>
        </div>
    @endif

    @if ($settings->invoice_terms_es || $settings->invoice_terms_en)
        <div class="terms">
            <h4>Terms &amp; Conditions</h4>
            @if ($settings->invoice_terms_es)<p>{{ $settings->invoice_terms_es }}</p>@endif
            @if ($settings->invoice_terms_en)<p>{{ $settings->invoice_terms_en }}</p>@endif
        </div>
    @endif
</body>
</html>
