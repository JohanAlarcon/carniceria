<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name_es',
        'name_en',
        'slug',
        'english_cut',
        'description_es',
        'description_en',
        'icon',
        'image',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function availableVariants(): HasMany
    {
        return $this->variants()->where('is_available', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Nombre en el idioma activo. */
    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? $this->name_en : $this->name_es;
    }

    public function description(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? $this->description_en : $this->description_es;
    }

    /** Precio base más bajo entre las variantes disponibles. */
    public function lowestPrice(): ?float
    {
        $price = $this->availableVariants()->min('base_price');

        return $price !== null ? (float) $price : null;
    }
}
