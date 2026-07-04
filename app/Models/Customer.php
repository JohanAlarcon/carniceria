<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'contact_name',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip',
        'price_adjustment_pct',
        'is_approved',
        'tax_exempt',
        'resale_certificate',
        'notes',
        'credit_enabled',
        'credit_limit',
        'credit_terms_days',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment_pct' => 'decimal:2',
            'is_approved' => 'boolean',
            'tax_exempt' => 'boolean',
            'credit_enabled' => 'boolean',
            'credit_limit' => 'decimal:2',
            'credit_terms_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /** Precio para este cliente sobre una variante (aplica su % de ajuste). */
    public function priceFor(ProductVariant $variant): float
    {
        return $variant->effectivePrice((float) $this->price_adjustment_pct);
    }

    public function fullAddress(): string
    {
        return collect([
            $this->address_line1,
            $this->address_line2,
            trim(collect([$this->city, $this->state, $this->zip])->filter()->implode(', ')),
        ])->filter()->implode("\n");
    }

    /** Días de plazo de crédito de este cliente (o el valor global por defecto). */
    public function creditTermsDays(): int
    {
        return $this->credit_terms_days ?? (int) BusinessSetting::current()->credit_terms_days;
    }

    /** Deuda de crédito viva: pedidos a crédito sin pagar y no cancelados. */
    public function outstandingCredit(): float
    {
        return (float) $this->orders()
            ->where('payment_method', 'credito')
            ->where('payment_status', 'pendiente')
            ->where('status', '!=', 'cancelado')
            ->sum('total');
    }

    /** Cupo de crédito disponible (nunca negativo). */
    public function availableCredit(): float
    {
        return max(0, (float) $this->credit_limit - $this->outstandingCredit());
    }
}
