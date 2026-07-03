<?php

namespace App\Support;

class Icons
{
    /** Claves de iconos disponibles (public/images/icons/<key>.webp). */
    public const KEYS = [
        'beef', 'pork', 'chicken', 'goat', 'lamb', 'fish', 'meat', 'grill',
        'steak', 'ground', 'tripe', 'bone', 'feet', 'ribs', 'liver', 'oxtail',
        'tongue', 'cheek', 'head', 'offal', 'sausage', 'ham', 'whole-chicken',
        'chicken-breast', 'chicken-wing', 'chicken-leg', 'chicken-feet',
        'seafood', 'crab', 'shrimp', 'octopus', 'quail', 'rabbit',
    ];

    public static function path(string $key): string
    {
        return 'images/icons/'.$key.'.webp';
    }

    /** [ruta => clave] para usar en selects de Filament. */
    public static function options(): array
    {
        $out = [];
        foreach (self::KEYS as $k) {
            $out[self::path($k)] = $k;
        }

        return $out;
    }
}
