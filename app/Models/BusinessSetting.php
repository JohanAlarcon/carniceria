<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'business_name',
        'legal_name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'email',
        'website',
        'logo_path',
        'tax_id',
        'invoice_prefix',
        'invoice_next_number',
        'order_next_number',
        'invoice_notes_es',
        'invoice_notes_en',
        'invoice_terms_es',
        'invoice_terms_en',
        'currency',
        'free_delivery',
        'default_shipping_fee',
        'min_order_amount',
    ];

    protected function casts(): array
    {
        return [
            'invoice_next_number' => 'integer',
            'order_next_number' => 'integer',
            'free_delivery' => 'boolean',
            'default_shipping_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
        ];
    }

    /** Fila única de configuración del negocio. */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
