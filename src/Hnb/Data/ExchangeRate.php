<?php

namespace Stboris\LaravelCroatiaToolkit\Hnb\Data;

final class ExchangeRate
{
    public function __construct(
        public readonly string $currency,
        public readonly string $date,
        public readonly float $buying,
        public readonly float $middle,
        public readonly float $selling,
        public readonly int $unit,
    ) {}

    public static function fromHnbResponse(array $data): self
    {
        return new self(
            currency: (string) $data['valuta'],
            date: (string) $data['datum_primjene'],
            buying: self::parseDecimal($data['kupovni_tecaj']),
            middle: self::parseDecimal($data['srednji_tecaj']),
            selling: self::parseDecimal($data['prodajni_tecaj']),
            unit: (int) ($data['jedinica'] ?? 1),
        );
    }

    private static function parseDecimal(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }
}
