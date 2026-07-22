<?php

namespace Stboris\LaravelCroatiaToolkit\Oib;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Stboris\LaravelCroatiaToolkit\Oib\Data\CompanyData;
use Stboris\LaravelCroatiaToolkit\Oib\Exceptions\SudskiRegistarException;

/**
 * OAuth2 client-credentials access to the Sudski registar open data API.
 *
 * The token endpoint (/api/oauth/token) is confirmed against the published
 * "Portal otvorenih podataka Sudskog registra" developer guide. The lookup
 * path below is this guide's documented method name (detalji_subjekta)
 * under the "javni" (public) prefix, but the exact REST route was not in
 * that guide — confirm it against the account's own OpenAPI spec at
 * {base}/api/javni/dokumentacija/open_api after registering, and adjust
 * lookupPath() if it differs.
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
        $response = Http::baseUrl($this->baseUrl())
            ->withToken($this->token())
            ->timeout(config('laravel-croatia-toolkit.http.timeout', 5))
            ->connectTimeout(config('laravel-croatia-toolkit.http.connect_timeout', 3))
            ->get($this->lookupPath(), ['oib' => $oib]);

        if ($response->status() === 404) {
            throw SudskiRegistarException::notFound($oib);
        }

        if ($response->failed()) {
            throw SudskiRegistarException::requestFailed($response->status());
        }

        return CompanyData::fromSudskiRegistarResponse($response->json());
    }

    protected function lookupPath(): string
    {
        return '/api/javni/subjekti/detalji_subjekta';
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
            ->timeout(config('laravel-croatia-toolkit.http.timeout', 5))
            ->post($this->baseUrl().'/api/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
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
