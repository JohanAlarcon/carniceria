<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'credit_terms_days',
        'delivery_start_time',
        'delivery_end_time',
        'delivery_min_lead_days',
        'delivery_min_lead_unit',
    ];

    protected function casts(): array
    {
        return [
            'invoice_next_number' => 'integer',
            'order_next_number' => 'integer',
            'free_delivery' => 'boolean',
            'default_shipping_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'credit_terms_days' => 'integer',
            'delivery_min_lead_days' => 'integer',
        ];
    }

    /** Fila única de configuración del negocio. */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    /**
     * Fecha/hora mínima de entrega según la anticipación configurada.
     * - unidad "días": desde el inicio del día (hoy + N días).
     * - unidad "horas": ahora + N horas; si cae fuera del horario de entrega,
     *   se ajusta a la apertura del mismo día o del día siguiente.
     */
    public function earliestDeliveryAt(): Carbon
    {
        $amount = max(0, (int) $this->delivery_min_lead_days);

        if ($this->delivery_min_lead_unit !== 'hours') {
            return now()->startOfDay()->addDays($amount);
        }

        $earliest = now()->addHours($amount);
        $start = substr((string) $this->delivery_start_time, 0, 5) ?: '08:00';
        $end = substr((string) $this->delivery_end_time, 0, 5) ?: '18:00';
        $time = $earliest->format('H:i');

        if ($time > $end) {
            return $earliest->addDay()->setTimeFromTimeString($start);
        }

        if ($time < $start) {
            return $earliest->setTimeFromTimeString($start);
        }

        return $earliest;
    }
}
