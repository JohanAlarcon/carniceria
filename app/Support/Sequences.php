<?php

namespace App\Support;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;

class Sequences
{
    /** Folio de pedido atómico: ORD-000001. */
    public static function nextOrderNumber(): string
    {
        return DB::transaction(function () {
            $s = BusinessSetting::query()->lockForUpdate()->firstOrCreate([]);
            $n = $s->order_next_number ?: 1;
            $s->order_next_number = $n + 1;
            $s->save();

            return 'ORD-'.str_pad((string) $n, 6, '0', STR_PAD_LEFT);
        });
    }

    /** Folio de factura atómico usando el prefijo configurado: INV-000001. */
    public static function nextInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $s = BusinessSetting::query()->lockForUpdate()->firstOrCreate([]);
            $n = $s->invoice_next_number ?: 1;
            $s->invoice_next_number = $n + 1;
            $s->save();

            return ($s->invoice_prefix ?: 'INV-').str_pad((string) $n, 6, '0', STR_PAD_LEFT);
        });
    }
}
