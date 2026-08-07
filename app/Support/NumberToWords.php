<?php
// app/Support/NumberToWords.php

namespace App\Support;

use NumberFormatter;

class NumberToWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    /**
     * Spell out a number in words, e.g. 167232 -> "One Hundred Sixty Seven Thousand Two
     * Hundred Thirty Two". Whole part only — pass the fractional part separately if needed
     * (invoice amounts use "AED X Only" / "X and Y Fils", grams use "X and Y Mg").
     */
    public static function words(float $number): string
    {
        $whole = (int) floor(abs($number));

        if ($whole === 0) {
            return 'Zero';
        }

        if (extension_loaded('intl')) {
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $words = $formatter->format($whole);

            if ($words !== false) {
                return ucwords(str_replace('-', ' ', $words));
            }
        }

        return self::spellFallback($whole);
    }

    private static function spellFallback(int $number): string
    {
        if ($number < 20) {
            return self::ONES[$number];
        }

        if ($number < 100) {
            $tens = self::TENS[intdiv($number, 10)];
            $rest = $number % 10;
            return $rest > 0 ? $tens . ' ' . self::ONES[$rest] : $tens;
        }

        if ($number < 1000) {
            $rest = $number % 100;
            return self::ONES[intdiv($number, 100)] . ' Hundred' . ($rest > 0 ? ' ' . self::spellFallback($rest) : '');
        }

        foreach ([
            1_000_000_000 => 'Billion',
            1_000_000     => 'Million',
            1_000         => 'Thousand',
        ] as $divisor => $label) {
            if ($number >= $divisor) {
                $rest = $number % $divisor;
                return self::spellFallback(intdiv($number, $divisor)) . ' ' . $label
                    . ($rest > 0 ? ' ' . self::spellFallback($rest) : '');
            }
        }

        return (string) $number;
    }
}
