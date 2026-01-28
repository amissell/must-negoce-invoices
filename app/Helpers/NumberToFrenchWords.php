    <?php
// app/Helpers/NumberToFrenchWords.php

namespace App\Helpers;

class NumberToFrenchWords
{
    private static array $units = [
        '', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF',
        'DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT',
        'DIX-HUIT', 'DIX-NEUF'
    ];

    private static array $tens = [
        '', '', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE',
        'SOIXANTE', 'QUATRE-VINGT', 'QUATRE-VINGT'
    ];

    /**
     * Convert a number to French words for invoice amounts
     */
    public static function convert(float $amount): string
    {
        $integerPart = (int) floor($amount);
        $decimalPart = (int) round(($amount - $integerPart) * 100);

        $result = self::convertInteger($integerPart) . ' DHS';

        if ($decimalPart > 0) {
            $result .= ' ET ' . self::convertInteger($decimalPart) . ' CENTIMES';
        }

        return $result;
    }

    private static function convertInteger(int $number): string
    {
        if ($number === 0) {
            return 'ZÉRO';
        }

        if ($number < 0) {
            return 'MOINS ' . self::convertInteger(abs($number));
        }

        $words = '';

        // Thousands
        if ($number >= 1000) {
            $thousands = (int) floor($number / 1000);
            if ($thousands === 1) {
                $words .= 'MILLE ';
            } else {
                $words .= self::convertInteger($thousands) . ' MILLE ';
            }
            $number %= 1000;
        }

        // Hundreds
        if ($number >= 100) {
            $hundreds = (int) floor($number / 100);
            if ($hundreds === 1) {
                $words .= 'CENT ';
            } else {
                $words .= self::$units[$hundreds] . ' CENT';
                if ($number % 100 === 0) {
                    $words .= 'S';
                }
                $words .= ' ';
            }
            $number %= 100;
        }

        // Tens and units
        if ($number > 0) {
            if ($number < 20) {
                $words .= self::$units[$number];
            } else {
                $ten = (int) floor($number / 10);
                $unit = $number % 10;

                if ($ten === 7 || $ten === 9) {
                    $words .= self::$tens[$ten];
                    if ($unit === 1 && $ten === 7) {
                        $words .= ' ET ';
                    } else {
                        $words .= '-';
                    }
                    $words .= self::$units[10 + $unit];
                } else {
                    $words .= self::$tens[$ten];
                    
                    if ($ten === 8 && $unit === 0) {
                        $words .= 'S';
                    }
                    
                    if ($unit > 0) {
                        if ($unit === 1 && $ten !== 8) {
                            $words .= ' ET ';
                        } else {
                            $words .= '-';
                        }
                        $words .= self::$units[$unit];
                    }
                }
            }
        }

        return trim($words);
    }
}