<?php

namespace App\Helpers;

class NumberToWords
{
    /**
     * Convertir un nombre en mots français
     */
    public static function convertToFrench($number)
    {
        $number = round($number, 2);
        $integerPart = floor($number);
        $decimalPart = round(($number - $integerPart) * 100);
        
        $result = self::convertIntegerToFrench($integerPart);
        
        if ($decimalPart > 0) {
            $result .= ' et ' . self::convertIntegerToFrench($decimalPart) . ' centimes';
        }
        
        return $result;
    }
    
    /**
     * Convertir un entier en mots français
     */
    private static function convertIntegerToFrench($number)
    {
        if ($number == 0) {
            return 'zéro';
        }
        
        $ones = [
            '', 'un', 'deux', 'trois', 'quatre', 'cinq',
            'six', 'sept', 'huit', 'neuf', 'dix',
            'onze', 'douze', 'treize', 'quatorze', 'quinze',
            'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
        ];
        
        $tens = [
            '', '', 'vingt', 'trente', 'quarante', 'cinquante',
            'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'
        ];
        
        $result = '';
        
        // Millions
        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            if ($millions == 1) {
                $result .= 'un million ';
            } else {
                $result .= self::convertIntegerToFrench($millions) . ' millions ';
            }
            $number %= 1000000;
        }
        
        // Milliers
        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            if ($thousands == 1) {
                $result .= 'mille ';
            } else {
                $result .= self::convertIntegerToFrench($thousands) . ' mille ';
            }
            $number %= 1000;
        }
        
        // Centaines
        if ($number >= 100) {
            $hundreds = floor($number / 100);
            if ($hundreds == 1) {
                $result .= 'cent ';
            } else {
                $result .= $ones[$hundreds] . ' cent ';
            }
            $number %= 100;
            
            // Pluriel "cents" si pas de reste
            if ($number == 0 && $hundreds > 1) {
                $result = rtrim($result) . 's ';
            }
        }
        
        // 70-79 (soixante-dix...)
        if ($number >= 70 && $number < 80) {
            $result .= 'soixante-';
            $number -= 60;
        }
        
        // 90-99 (quatre-vingt-dix...)
        if ($number >= 90) {
            $result .= 'quatre-vingt-';
            $number -= 80;
        }
        
        // 80-89 (quatre-vingt...)
        if ($number >= 80 && $number < 90) {
            $result .= 'quatre-vingt';
            $number -= 80;
            if ($number == 0) {
                $result .= 's';
            } else {
                $result .= '-';
            }
        }
        
        // 20-69
        if ($number >= 20 && $number < 70) {
            $tensDigit = floor($number / 10);
            $result .= $tens[$tensDigit];
            $number %= 10;
            
            if ($number == 1) {
                $result .= ' et un';
                $number = 0;
            } elseif ($number > 0) {
                $result .= '-';
            }
        }
        
        // 1-19
        if ($number > 0 && $number < 20) {
            $result .= $ones[$number];
        }
        
        return trim($result);
    }
    
    /**
     * Convertir en arabe (si besoin)
     */
    // public static function convertToArabic($number)
    // {
    //     // La même fonction qu'avant si vous voulez garder l'arabe
    //     $number = round($number, 2);
    //     $integerPart = floor($number);
    //     $decimalPart = round(($number - $integerPart) * 100);
        
    //     $result = self::convertIntegerToArabic($integerPart);
        
    //     if ($decimalPart > 0) {
    //         $result .= ' و ' . self::convertIntegerToArabic($decimalPart) . ' سنتيماً';
    //     }
        
    //     return $result;
    // }
    
    // private static function convertIntegerToArabic($number)
    // {
    //     if ($number == 0) {
    //         return 'صفر';
    //     }
        
    //     $ones = [
    //         '', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة',
    //         'ستة', 'سبعة', 'ثمانية', 'تسعة'
    //     ];
        
    //     $tens = [
    //         '', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون',
    //         'ستون', 'سبعون', 'ثمانون', 'تسعون'
    //     ];
        
    //     $hundreds = [
    //         '', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة',
    //         'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'
    //     ];
        
    //     $teens = [
    //         'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر',
    //         'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'
    //     ];
        
    //     $result = '';
        
    //     if ($number >= 1000000) {
    //         $millions = floor($number / 1000000);
    //         if ($millions == 1) {
    //             $result .= 'مليون ';
    //         } elseif ($millions == 2) {
    //             $result .= 'مليونان ';
    //         } else {
    //             $result .= self::convertIntegerToArabic($millions) . ' ملايين ';
    //         }
    //         $number %= 1000000;
    //     }
        
    //     if ($number >= 1000) {
    //         $thousands = floor($number / 1000);
    //         if ($thousands == 1) {
    //             $result .= 'ألف ';
    //         } elseif ($thousands == 2) {
    //             $result .= 'ألفان ';
    //         } else {
    //             $result .= self::convertIntegerToArabic($thousands) . ' ألف ';
    //         }
    //         $number %= 1000;
    //     }
        
    //     if ($number >= 100) {
    //         $hundredsDigit = floor($number / 100);
    //         $result .= $hundreds[$hundredsDigit] . ' ';
    //         $number %= 100;
    //         if ($number > 0) {
    //             $result .= 'و ';
    //         }
    //     }
        
    //     if ($number >= 10 && $number < 20) {
    //         $result .= $teens[$number - 10];
    //         return trim($result);
    //     }
        
    //     if ($number >= 20) {
    //         $tensDigit = floor($number / 10);
    //         $result .= $tens[$tensDigit];
    //         $number %= 10;
    //         if ($number > 0) {
    //             $result .= ' و ';
    //         }
    //     }
        
    //     if ($number > 0 && $number < 10) {
    //         $result .= $ones[$number];
    //     }
        
    //     return trim($result);
    // }
}