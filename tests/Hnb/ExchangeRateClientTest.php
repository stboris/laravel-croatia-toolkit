<?php

use Illuminate\Support\Facades\Http;
use Stboris\LaravelCroatiaToolkit\Hnb\Exceptions\HnbException;
use Stboris\LaravelCroatiaToolkit\Hnb\ExchangeRateClient;

function fakeHnbList(): array
{
    return [
        [
            'broj_tecajnice' => '141',
            'datum_primjene' => '2026-07-22',
            'drzava' => 'Sjedinjene Američke Države',
            'drzava_iso' => 'USA',
            'kupovni_tecaj' => '1,150000',
            'prodajni_tecaj' => '1,140000',
            'sifra_valute' => '840',
            'srednji_tecaj' => '1,145000',
            'valuta' => 'USD',
        ],
        [
            'broj_tecajnice' => '141',
            'datum_primjene' => '2026-07-22',
            'drzava' => 'Švicarska',
            'drzava_iso' => 'CHE',
            'kupovni_tecaj' => '0,927300',
            'prodajni_tecaj' => '0,924500',
            'sifra_valute' => '756',
            'srednji_tecaj' => '0,925900',
            'valuta' => 'CHF',
        ],
    ];
}

it('lists exchange rates and parses comma decimals', function () {
    Http::fake(['api.hnb.hr/*' => Http::response(fakeHnbList())]);

    $rates = (new ExchangeRateClient)->list();

    expect($rates)->toHaveCount(2)
        ->and($rates[0]->currency)->toBe('USD')
        ->and($rates[0]->middle)->toBe(1.145);
});

it('finds a single currency client-side', function () {
    Http::fake(['api.hnb.hr/*' => Http::response(fakeHnbList())]);

    $rate = (new ExchangeRateClient)->rate('chf');

    expect($rate)->not->toBeNull()
        ->and($rate->currency)->toBe('CHF')
        ->and($rate->selling)->toBe(0.9245);
});

it('returns null for an unlisted currency', function () {
    Http::fake(['api.hnb.hr/*' => Http::response(fakeHnbList())]);

    expect((new ExchangeRateClient)->rate('JPY'))->toBeNull();
});

it('throws on a failed request', function () {
    Http::fake(['api.hnb.hr/*' => Http::response(null, 500)]);

    (new ExchangeRateClient)->list();
})->throws(HnbException::class);
