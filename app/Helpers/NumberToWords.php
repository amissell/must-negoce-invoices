<?php

namespace App\Helpers;

class NumberToWords
{
    private static $units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix',
        'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
    ];

    private static $tens = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'
    ];

    public static function convert($number)
    {
        $number = floatval($number);
        $intPart = floor($number);
        $decPart = round(($number - $intPart) * 100);

        $words = self::convertIntegerPart($intPart);
        $words .= ' dirhams';

        if ($decPart > 0) {
            $words .= ' et ' . self::convertIntegerPart($decPart) . ' centimes';
        }

        return ucfirst($words);
    }

    private static function convertIntegerPart($number)
    {
        if ($number == 0) {
            return 'zéro';
        }

        $words = '';

        // Millions
        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            if ($millions == 1) {
                $words .= 'un million ';
            } else {
                $words .= self::convertHundreds($millions) . ' millions ';
            }
            $number %= 1000000;
        }

        // Thousands
        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            if ($thousands == 1) {
                $words .= 'mille ';
            } else {
                $words .= self::convertHundreds($thousands) . ' mille ';
            }
            $number %= 1000;
        }

        // Hundreds
        if ($number > 0) {
            $words .= self::convertHundreds($number);
        }

        return trim($words);
    }

    private static function convertHundreds($number)
    {
        $words = '';

        if ($number >= 100) {
            $hundreds = floor($number / 100);
            if ($hundreds == 1) {
                $words .= 'cent ';
            } else {
                $words .= self::$units[$hundreds] . ' cent ';
            }
            $number %= 100;
            
            if ($number == 0 && $hundreds > 1) {
                $words = rtrim($words) . 's ';
            }
        }

        if ($number >= 20) {
            $tens = floor($number / 10);
            $units = $number % 10;
            
            $words .= self::$tens[$tens];
            
            if ($units > 0) {
                if ($tens == 7 || $tens == 9) {
                    $words .= '-' . self::$units[$units + 10];
                } else if ($units == 1 && $tens < 8) {
                    $words .= ' et ' . self::$units[$units];
                } else {
                    $words .= '-' . self::$units[$units];
                }
            } else if ($tens == 8) {
                $words .= 's';
            }
            
            $words .= ' ';
        } else if ($number > 0) {
            $words .= self::$units[$number] . ' ';
        }

        return trim($words);
    }
}