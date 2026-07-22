<?php

use Illuminate\Support\Facades\Http;
use Stboris\LaravelCroatiaToolkit\Oib\Exceptions\SudskiRegistarException;
use Stboris\LaravelCroatiaToolkit\Oib\SudskiRegistarClient;

function sudregClient(): SudskiRegistarClient
{
    return new SudskiRegistarClient('https://sudreg-data-test.gov.hr', 'client-id', 'client-secret');
}

it('authenticates and looks up a company by OIB', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/subjekti/detalji_subjekta*' => Http::response([
            'oib' => '69435151530',
            'mbs' => '080000014',
            'tvrtka' => 'Primjer d.o.o.',
            'adresa' => 'Ilica 1, Zagreb',
            'pravni_oblik' => 'Društvo s ograničenom odgovornošću',
            'status' => 'AKTIVAN',
        ]),
    ]);

    $company = sudregClient()->lookup('69435151530');

    expect($company->name)->toBe('Primjer d.o.o.')
        ->and($company->mbs)->toBe('080000014')
        ->and($company->active)->toBeTrue();

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer token-123'));
});

it('marks a BRISAN subject as inactive', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/subjekti/detalji_subjekta*' => Http::response([
            'oib' => '69435151530',
            'tvrtka' => 'Primjer d.o.o.',
            'status' => 'BRISAN',
        ]),
    ]);

    expect(sudregClient()->lookup('69435151530')->active)->toBeFalse();
});

it('throws when client credentials are not configured', function () {
    (new SudskiRegistarClient('https://sudreg-data-test.gov.hr'))->lookup('69435151530');
})->throws(SudskiRegistarException::class);

it('throws when the subject is not found', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/subjekti/detalji_subjekta*' => Http::response(null, 404),
    ]);

    sudregClient()->lookup('69435151530');
})->throws(SudskiRegistarException::class);

it('reuses the cached access token across calls', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/subjekti/detalji_subjekta*' => Http::response([
            'oib' => '69435151530',
            'tvrtka' => 'Primjer d.o.o.',
        ]),
    ]);

    $client = sudregClient();
    $client->lookup('69435151530');
    $client->lookup('69435151530');

    Http::assertSentCount(3);
});
