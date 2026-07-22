<?php

use Illuminate\Support\Facades\Http;
use Stboris\LaravelCroatiaToolkit\Oib\Exceptions\SudskiRegistarException;
use Stboris\LaravelCroatiaToolkit\Oib\SudskiRegistarClient;

function sudregClient(): SudskiRegistarClient
{
    return new SudskiRegistarClient('https://sudreg-data-test.gov.hr', 'client-id', 'client-secret');
}

/**
 * Shaped after a real, live response verified against the test environment
 * (2026-07-22, subject: Zagrebačka banka d.d., mbs 080000014).
 */
function fakeSubjectResponse(array $overrides = []): array
{
    return array_merge([
        'mbs' => 80000014,
        'status' => 1,
        'oib' => 92963223473,
        'potpuni_mbs' => '080000014',
        'potpuni_oib' => '92963223473',
        'tvrtka' => ['ime' => 'ZAGREBAČKA BANKA DIONIČKO DRUŠTVO'],
        'sjediste' => [
            'naziv_naselja' => 'Zagreb',
            'ulica' => 'Trg bana Josipa Jelačića',
            'kucni_broj' => 10,
        ],
    ], $overrides);
}

it('authenticates via HTTP basic auth and looks up a company by OIB', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/detalji_subjekta*' => Http::response(fakeSubjectResponse()),
    ]);

    $company = sudregClient()->lookup('92963223473');

    expect($company->name)->toBe('ZAGREBAČKA BANKA DIONIČKO DRUŠTVO')
        ->and($company->mbs)->toBe('080000014')
        ->and($company->address)->toBe('Trg bana Josipa Jelačića 10, Zagreb')
        ->and($company->active)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer token-123')
            && $request['tip_identifikatora'] === 'oib'
            && $request['identifikator'] === '92963223473';
    });

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/api/oauth/token')
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('client-id:client-secret'));
    });
});

it('marks an inactive (non-status-1) subject as inactive', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/detalji_subjekta*' => Http::response(fakeSubjectResponse(['status' => 0])),
    ]);

    expect(sudregClient()->lookup('92963223473')->active)->toBeFalse();
});

it('throws when client credentials are not configured', function () {
    (new SudskiRegistarClient('https://sudreg-data-test.gov.hr'))->lookup('69435151530');
})->throws(SudskiRegistarException::class);

it('throws when the subject is not found (empty object response)', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/detalji_subjekta*' => Http::response([]),
    ]);

    sudregClient()->lookup('69435151530');
})->throws(SudskiRegistarException::class);

it('reuses the cached access token across calls', function () {
    Http::fake([
        'sudreg-data-test.gov.hr/api/oauth/token' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
        'sudreg-data-test.gov.hr/api/javni/detalji_subjekta*' => Http::response(fakeSubjectResponse()),
    ]);

    $client = sudregClient();
    $client->lookup('92963223473');
    $client->lookup('92963223473');

    Http::assertSentCount(3);
});
