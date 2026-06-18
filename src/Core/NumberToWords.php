<?php

namespace App\Core;

/**
 * NumberToWords
 * 
 * Classe utilitaire pour convertir un montant numérique en lettres en français.
 */
class NumberToWords
{
    private static $units = [
        0 => 'zéro', 1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre',
        5 => 'cinq', 6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf',
        10 => 'dix', 11 => 'onze', 12 => 'douze', 13 => 'treize',
        14 => 'quatorze', 15 => 'quinze', 16 => 'seize', 17 => 'dix-sept',
        18 => 'dix-huit', 19 => 'dix-neuf'
    ];

    private static $tens = [
        10 => 'dix', 20 => 'vingt', 30 => 'trente', 40 => 'quarante',
        50 => 'cinquante', 60 => 'soixante', 70 => 'soixante-dix',
        80 => 'quatre-vingt', 90 => 'quatre-vingt-dix'
    ];

    /**
     * Convertit un entier en mots français.
     * 
     * @param int $number
     * @return string
     */
    public static function convert($number)
    {
        $number = (int)$number;
        if ($number < 0) {
            return 'moins ' . self::convert(-$number);
        }

        if ($number < 20) {
            return self::$units[$number];
        }

        if ($number < 100) {
            $ten = (int)($number / 10) * 10;
            $unit = $number % 10;

            if ($ten === 70) {
                if ($unit === 1) {
                    return 'soixante et onze';
                }
                return 'soixante-' . self::convert(10 + $unit);
            }

            if ($ten === 90) {
                return 'quatre-vingt-' . self::convert(10 + $unit);
            }

            if ($unit === 0) {
                if ($ten === 80) {
                    return 'quatre-vingts';
                }
                return self::$tens[$ten];
            }

            if ($unit === 1) {
                return self::$tens[$ten] . ' et un';
            }

            return self::$tens[$ten] . '-' . self::$units[$unit];
        }

        if ($number < 1000) {
            $hundred = (int)($number / 100);
            $rest = $number % 100;

            $hundredWord = ($hundred === 1) ? 'cent' : self::$units[$hundred] . ' cent';
            
            // "deux cents" vs "deux cent trois"
            if ($hundred > 1 && $rest === 0) {
                $hundredWord .= 's';
            }

            if ($rest === 0) {
                return $hundredWord;
            }

            return $hundredWord . ' ' . self::convert($rest);
        }

        if ($number < 1000000) {
            $thousand = (int)($number / 1000);
            $rest = $number % 1000;

            $thousandWord = ($thousand === 1) ? 'mille' : self::convert($thousand) . ' mille';

            if ($rest === 0) {
                return $thousandWord;
            }

            return $thousandWord . ' ' . self::convert($rest);
        }

        if ($number < 1000000000) {
            $million = (int)($number / 1000000);
            $rest = $number % 1000000;

            $millionWord = self::convert($million) . ' million';
            if ($million > 1) {
                $millionWord .= 's';
            }

            if ($rest === 0) {
                return $millionWord;
            }

            return $millionWord . ' ' . self::convert($rest);
        }

        $milliard = (int)($number / 1000000000);
        $rest = $number % 1000000000;

        $milliardWord = self::convert($milliard) . ' milliard';
        if ($milliard > 1) {
            $milliardWord .= 's';
        }

        if ($rest === 0) {
            return $milliardWord;
        }

        return $milliardWord . ' ' . self::convert($rest);
    }

    /**
     * Formate le montant complet en toutes lettres avec devise.
     * 
     * @param float|int $amount
     * @param string $currency
     * @return string
     */
    public static function toWords($amount, $currency = 'FCFA')
    {
        $amount = (int)$amount;
        if ($amount === 0) {
            return 'zéro franc CFA';
        }

        $words = self::convert($amount);
        
        // Capitaliser la première lettre
        $words = mb_strtoupper(mb_substr($words, 0, 1)) . mb_substr($words, 1);
        
        if ($currency === 'FCFA') {
            return $words . ' francs CFA';
        }
        
        return $words . ' ' . $currency;
    }
}
