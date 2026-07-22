<?php

namespace Stboris\LaravelCroatiaToolkit\Oib;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Stboris\LaravelCroatiaToolkit\Oib\Data\CompanyData;
use Stboris\LaravelCroatiaToolkit\Oib\Exceptions\SudskiRegistarException;

/**
 * OAuth2 client-credentials access to the Sudski registar open data API,
 * verified live against the test environment (2026-07-22).
 *
 * Token endpoint uses HTTP Basic Auth (client_id:client_secret), not
 * client credentials in the POST body. The lookup endpoint lives on a
 * different host path than the token endpoint (/api/javni/detalji_subjekta,
 * not under /api/javni/subjekti/...), takes tip_identifikatora + identifikator
 * query params, and — with no_data_error=0 — returns an empty JSON object
 * (not a 404) when nothing matches.
 */
class SudskiRegistarClient
{
    public function __construct(
        protected readonly ?string $baseUrl = null,
        protected readonly ?string $clientId = null,
        protected readonly ?string $clientSecret = null,
    ) {}

    public function lookup(string $oib): CompanyData
    {
        $response = Http::baseUrl($this->baseUrl().'/api/javni')
            ->withToken($this->token())
            ->timeout(config('laravel-croatia-toolkit.http.timeout', 5))
            ->connectTimeout(config('laravel-croatia-toolkit.http.connect_timeout', 3))
            ->get('/detalji_subjekta', [
                'tip_identifikatora' => 'oib',
                'identifikator' => $oib,
                'no_data_error' => 0,
            ]);

        if ($response->failed()) {
            throw SudskiRegistarException::requestFailed($response->status());
        }

        $data = $response->json();

        if (empty($data['oib'])) {
            throw SudskiRegistarException::notFound($oib);
        }

        return CompanyData::fromSudskiRegistarResponse($data);
    }

    protected function token(): string
    {
        $clientId = $this->clientId ?? config('laravel-croatia-toolkit.sudski_registar.client_id');
        $clientSecret = $this->clientSecret ?? config('laravel-croatia-toolkit.sudski_registar.client_secret');

        if (! $clientId || ! $clientSecret) {
            throw SudskiRegistarException::notConfigured();
        }

        $cacheKey = 'laravel-croatia-toolkit:sudreg-token:'.md5($clientId);

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->timeout(config('laravel-croatia-toolkit.http.timeout', 5))
            ->post($this->baseUrl().'/api/oauth/token', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw SudskiRegistarException::authenticationFailed($response->status());
        }

        $token = $response->json('access_token');
        $expiresIn = (int) ($response->json('expires_in') ?? 3600);

        Cache::put($cacheKey, $token, max($expiresIn - 60, 30));

        return $token;
    }

    protected function baseUrl(): string
    {
        return $this->baseUrl ?? config('laravel-croatia-toolkit.sudski_registar.base_url');
    }
}
