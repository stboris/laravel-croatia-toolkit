<?php

namespace Stboris\LaravelCroatiaToolkit\Oib\Data;

final class CompanyData
{
    public function __construct(
        public readonly string $oib,
        public readonly ?string $mbs,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?string $legalForm,
        public readonly bool $active,
    ) {}

    public static function fromSudskiRegistarResponse(array $data): self
    {
        return new self(
            oib: (string) ($data['oib'] ?? ''),
            mbs: isset($data['mbs']) ? (string) $data['mbs'] : null,
            name: (string) ($data['tvrtka'] ?? $data['naziv'] ?? ''),
            address: $data['adresa'] ?? null,
            legalForm: $data['pravni_oblik'] ?? null,
            active: ($data['status'] ?? null) !== 'BRISAN',
        );
    }
}
