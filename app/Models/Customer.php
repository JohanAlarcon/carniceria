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
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment_pct' => 'decimal:2',
            'is_approved' => 'boolean',
            'tax_exempt' => 'boolean',
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
}
