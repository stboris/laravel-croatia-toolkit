<?php

namespace Stboris\LaravelCroatiaToolkit\Oib\Data;

final class CompanyData
{
    public function __construct(
        public readonly string $oib,
        public readonly ?string $mbs,
        public readonly string $name,
        public readonly ?string $address,
        public readonly bool $active,
    ) {}

    /**
     * Maps the real detalji_subjekta response shape, verified live against
     * the Sudski registar test environment (2026-07-22) — company name and
     * address are nested objects, not flat keys, and status is an integer
     * (1 = active).
     */
    public static function fromSudskiRegistarResponse(array $data): self
    {
        return new self(
            oib: (string) ($data['potpuni_oib'] ?? $data['oib'] ?? ''),
            mbs: isset($data['potpuni_mbs']) ? (string) $data['potpuni_mbs'] : null,
            name: (string) ($data['tvrtka']['ime'] ?? ''),
            address: self::formatAddress($data['sjediste'] ?? []),
            active: (int) ($data['status'] ?? 0) === 1,
        );
    }

    private static function formatAddress(array $sjediste): ?string
    {
        $street = trim(($sjediste['ulica'] ?? '').' '.($sjediste['kucni_broj'] ?? ''));
        $place = trim(($sjediste['postanski_broj'] ?? '').' '.($sjediste['naziv_naselja'] ?? ''));

        $parts = array_filter([$street, $place]);

        return $parts ? implode(', ', $parts) : null;
    }
}
