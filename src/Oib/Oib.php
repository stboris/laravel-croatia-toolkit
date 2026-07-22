<?php

namespace Stboris\LaravelCroatiaToolkit\Oib;

/**
 * OIB checksum per ISO 7064 MOD 11,10, as specified by Porezna uprava.
 */
final class Oib
{
    public static function isValid(?string $oib): bool
    {
        if ($oib === null || ! preg_match('/^\d{11}$/', $oib)) {
            return false;
        }

        return self::checkDigit(substr($oib, 0, 10)) === (int) $oib[10];
    }

    public static function checkDigit(string $firstTenDigits): int
    {
        $a = 10;

        foreach (str_split($firstTenDigits) as $digit) {
            $a = ($a + (int) $digit) % 10;

            if ($a === 0) {
                $a = 10;
            }

            $a = ($a * 2) % 11;
        }

        return (11 - $a) % 10;
    }
}
