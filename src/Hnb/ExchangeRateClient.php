<?php

namespace Stboris\LaravelCroatiaToolkit\Hnb;

use Illuminate\Support\Facades\Http;
use Stboris\LaravelCroatiaToolkit\Hnb\Data\ExchangeRate;
use Stboris\LaravelCroatiaToolkit\Hnb\Exceptions\HnbException;

/**
 * Public, keyless access to HNB's daily reference exchange rate list
 * (api.hnb.hr/tecajn-eur/v3 — the live post-2023 endpoint; the older
 * /tecajn/v2 path stopped updating when Croatia adopted the euro).
 *
 * The API's own currency filter was unreliable in testing, so the full
 * daily list is fetched and filtered client-side.
 */
class ExchangeRateClient
{
    public function __construct(protected readonly ?string $baseUrl = null) {}

    /**
     * @return ExchangeRate[]
     */
    public function list(?string $date = null): array
    {
        $query = ['format' => 'json'];

        if ($date) {
            $query['datum-primjene'] = $date;
        }

        $response = Http::timeout(config('laravel-croatia-toolkit.http.timeout', 5))
            ->connectTimeout(config('laravel-croatia-toolkit.http.connect_timeout', 3))
            ->get($this->baseUrl(), $query);

        if ($response->failed()) {
            throw HnbException::requestFailed($response->status());
        }

        return array_map(
            fn (array $row) => ExchangeRate::fromHnbResponse($row),
            $response->json() ?? [],
        );
    }

    public function rate(string $currency, ?string $date = null): ?ExchangeRate
    {
        $currency = strtoupper($currency);

        foreach ($this->list($date) as $rate) {
            if ($rate->currency === $currency) {
                return $rate;
            }
        }

        return null;
    }

    protected function baseUrl(): string
    {
        return $this->baseUrl ?? config('laravel-croatia-toolkit.hnb.base_url');
    }
}
