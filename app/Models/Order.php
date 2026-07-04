<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pendiente',
        'confirmado',
        'en_preparacion',
        'en_ruta',
        'entregado',
        'cancelado',
    ];

    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'subtotal',
        'shipping_fee',
        'tax',
        'total',
        'price_adjustment_pct',
        'delivery_business_name',
        'delivery_contact_name',
        'delivery_phone',
        'delivery_address_line1',
        'delivery_address_line2',
        'delivery_city',
        'delivery_state',
        'delivery_zip',
        'requested_date',
        'requested_at',
        'delivery_notes',
        'internal_notes',
        'payment_method',
        'payment_status',
        'payment_due_date',
        'credit_terms_days',
        'paid_at',
        'invoice_number',
        'invoiced_at',
        'placed_at',
        'confirmed_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'price_adjustment_pct' => 'decimal:2',
            'requested_date' => 'date',
            'requested_at' => 'datetime',
            'payment_due_date' => 'date',
            'paid_at' => 'datetime',
            'invoiced_at' => 'datetime',
            'placed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'en_preparacion' => 'En preparación',
            'en_ruta' => 'En ruta',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pendiente' => 'warning',
            'confirmado' => 'info',
            'en_preparacion' => 'primary',
            'en_ruta' => 'info',
            'entregado' => 'success',
            'cancelado' => 'danger',
            default => 'gray',
        };
    }

    public function isCredit(): bool
    {
        return $this->payment_method === 'credito';
    }

    public function paymentMethodLabel(): string
    {
        return $this->isCredit() ? 'Crédito' : 'Contraentrega';
    }

    public function paymentStatusLabel(): string
    {
        return $this->payment_status === 'pagado' ? 'Pagado' : 'Pendiente';
    }
}
