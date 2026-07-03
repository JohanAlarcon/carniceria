<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'label_es',
        'label_en',
        'brand',
        'grade',
        'is_frozen',
        'sku',
        'unit',
        'unit_label_es',
        'unit_label_en',
        'package_weight_lb',
        'base_price',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_frozen' => 'boolean',
            'is_available' => 'boolean',
            'package_weight_lb' => 'decimal:2',
            'base_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? ($this->label_en ?: $this->label_es) : $this->label_es;
    }

    public function unitLabel(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? $this->unit_label_en : $this->unit_label_es;
    }

    /** Precio unitario aplicando el % de ajuste del cliente. */
    public function effectivePrice(float $adjustmentPct = 0): float
    {
        return round(((float) $this->base_price) * (1 + ($adjustmentPct / 100)), 2);
    }
}
