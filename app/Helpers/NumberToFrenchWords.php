<?php

namespace App\Helpers;

class NumberToFrenchWords
{
    private static $unites = [
        '', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF',
        'DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE',
        'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF'
    ];

    private static $dizaines = [
        '', '', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE',
        'SOIXANTE', 'SOIXANTE', 'QUATRE-VINGT', 'QUATRE-VINGT'
    ];

    public static function convert($number)
    {
        $number = number_format($number, 2, '.', '');
        [$integerPart, $decimalPart] = explode('.', $number);
        
        $integerPart = (int) $integerPart;
        $decimalPart = (int) $decimalPart;

        $result = self::convertInteger($integerPart) . ' DHS';

        if ($decimalPart > 0) {
            $result .= ' ET ' . self::convertInteger($decimalPart) . ' CENTIMES';
        }

        return $result;
    }

    private static function convertInteger($number)
    {
        if ($number == 0) {
            return 'ZÉRO';
        }

        $result = '';

        // Millions
        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            if ($millions == 1) {
                $result .= 'UN MILLION ';
            } else {
                $result .= self::convertHundreds($millions) . ' MILLIONS ';
            }
            $number %= 1000000;
        }

        // Milliers
        if ($number >= 1000) {
            $milliers = floor($number / 1000);
            if ($milliers == 1) {
                $result .= 'MILLE ';
            } else {
                $result .= self::convertHundreds($milliers) . ' MILLE ';
            }
            $number %= 1000;
        }

        // Centaines
        if ($number > 0) {
            $result .= self::convertHundreds($number);
        }

        return trim($result);
    }

    private static function convertHundreds($number)
    {
        $result = '';

        // Centaines
        if ($number >= 100) {
            $centaines = floor($number / 100);
            if ($centaines == 1) {
                $result .= 'CENT ';
            } else {
                $result .= self::$unites[$centaines] . ' CENT ';
            }
            $number %= 100;
            
            // Accord du pluriel pour "CENT"
            if ($number == 0 && $centaines > 1) {
                $result = rtrim($result) . 'S ';
            }
        }

        // Dizaines et unités
        if ($number >= 20) {
            $dizaine = floor($number / 10);
            $unite = $number % 10;

            if ($dizaine == 7 || $dizaine == 9) {
                // 70-79 et 90-99
                $result .= self::$dizaines[$dizaine];
                if ($unite == 1) {
                    $result .= ' ET ' . self::$unites[10 + $unite];
                } else {
                    $result .= '-' . self::$unites[10 + $unite];
                }
            } else {
                // 20-69 et 80-89
                $result .= self::$dizaines[$dizaine];
                if ($unite > 0) {
                    if ($unite == 1 && $dizaine != 8) {
                        $result .= ' ET ' . self::$unites[$unite];
                    } else {
                        $result .= '-' . self::$unites[$unite];
                    }
                } elseif ($dizaine == 8) {
                    $result .= 'S';
                }
            }
        } elseif ($number > 0) {
            $result .= self::$unites[$number];
        }

        return trim($result);
    }
}