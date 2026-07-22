<?php

namespace Stboris\LaravelCroatiaToolkit\Iban;

/**
 * Generic ISO 13616 (MOD-97-10) IBAN checksum, plus a Croatia-specific
 * shape check: "HR" + 2 check digits + 7-digit bank code + 10-digit
 * account number = 21 characters, all-numeric after the country code.
 */
final class Iban
{
    public static function isValid(string $iban): bool
    {
        $iban = self::normalize($iban);

        if (strlen($iban) < 4 || ! preg_match('/^[A-Z0-9]+$/', $iban)) {
            return false;
        }

        $rearranged = substr($iban, 4).substr($iban, 0, 4);

        $numeric = '';

        foreach (str_split($rearranged) as $char) {
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

        return self::mod97($numeric) === 1;
    }

    public static function isValidCroatian(string $iban): bool
    {
        $iban = self::normalize($iban);

        return str_starts_with($iban, 'HR')
            && strlen($iban) === 21
            && ctype_digit(substr($iban, 2))
            && self::isValid($iban);
    }

    private static function normalize(string $iban): string
    {
        return strtoupper(str_replace(' ', '', $iban));
    }

    private static function mod97(string $numeric): int
    {
        $remainder = 0;

        foreach (str_split($numeric) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $remainder;
    }
}
