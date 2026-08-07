<?php

// app/Support/BarWeightPresets.php

namespace App\Support;

class BarWeightPresets
{
    /**
     * Common bar/coin sizes for inventory item creation, in grams. Includes the Tola/TT
     * Bar sizes standard in the UAE and South Asian gold trade alongside plain gram sizes.
     */
    public static function all(): array
    {
        return [
            ['label' => '1 Gram', 'grams' => 1],
            ['label' => '2 Grams', 'grams' => 2],
            ['label' => '5 Grams', 'grams' => 5],
            ['label' => '10 Grams', 'grams' => 10],
            ['label' => '20 Grams', 'grams' => 20],
            ['label' => '50 Grams', 'grams' => 50],
            ['label' => '100 Grams', 'grams' => 100],
            ['label' => '500 Grams', 'grams' => 500],
            ['label' => '1 Tola (11.6638g)', 'grams' => 11.6638],
            ['label' => 'TT Bar / 10 Tola (116.638g)', 'grams' => 116.638],
            ['label' => '1 Kilogram Bar', 'grams' => 1000],
        ];
    }

    /**
     * Short display label for a nominal weight (e.g. "10 Grams" or "TT Bar / 10 Tola") for
     * catalog/inventory display — falls back to a plain "{grams}g" for weights that don't
     * match a standard preset.
     */
    public static function labelFor(?float $grams): ?string
    {
        if ($grams === null) {
            return null;
        }

        foreach (self::all() as $preset) {
            if (abs($preset['grams'] - $grams) < 0.0005) {
                return $preset['label'];
            }
        }

        return rtrim(rtrim(number_format($grams, 4), '0'), '.').'g';
    }
}
