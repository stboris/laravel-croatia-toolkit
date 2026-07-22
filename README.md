# Laravel Croatia Toolkit

Small, focused Croatian business utilities for Laravel: OIB validation and
Sudski registar (court register) company lookup, HNB exchange rates, and
Croatian IBAN validation — with an optional Filament v5 form field for OIB
autofill.

Free and MIT-licensed. No Croatian company registration is required to use
or test any of it.

## Installation

```bash
composer require stboris/laravel-croatia-toolkit
php artisan vendor:publish --tag="croatia-toolkit-config"
```

## OIB

Checksum validation (ISO 7064 MOD 11,10) needs no configuration or network
access:

```php
use Stboris\LaravelCroatiaToolkit\Oib\Oib;

Oib::isValid('69435151530'); // true
```

As a validation rule:

```php
use Stboris\LaravelCroatiaToolkit\Oib\Rules\ValidOib;

$request->validate([
    'oib' => ['required', new ValidOib],
]);
```

### Company lookup (Sudski registar)

Looking up a company's name/address by OIB additionally needs a free
client id/secret from the Sudski registar open data portal — no company
registration required, just a valid email:

- Test: https://sudreg-data-test.gov.hr
- Production: https://sudreg-data.gov.hr

```env
CROATIA_TOOLKIT_SUDREG_CLIENT_ID=
CROATIA_TOOLKIT_SUDREG_CLIENT_SECRET=
```

```php
use Stboris\LaravelCroatiaToolkit\Oib\SudskiRegistarClient;

$company = app(SudskiRegistarClient::class)->lookup('69435151530');

$company->name;
$company->address;
$company->active;
```

> **Note:** the OAuth2 token endpoint is confirmed against the portal's own
> developer guide. The lookup path in `SudskiRegistarClient::lookupPath()`
> is a best-effort guess based on that guide's documented method name
> (`detalji_subjekta`) — confirm it against your own account's OpenAPI spec
> (`{base}/api/javni/dokumentacija/open_api`, visible after registering)
> before relying on it, and adjust `lookupPath()` if it differs.

### Filament form field

Requires `filament/filament`. Validates the OIB as you type and, once
valid, autofills sibling fields from the Sudski registar:

```php
use Stboris\LaravelCroatiaToolkit\Oib\Filament\OibField;

OibField::make('oib')
    ->autofill(name: 'company_name', address: 'company_address'),
```

## HNB exchange rates

Public, keyless API — no configuration needed. Uses the live
`api.hnb.hr/tecajn-eur/v3` endpoint (the older `/tecajn/v2` path stopped
updating when Croatia adopted the euro in 2023).

```php
use Stboris\LaravelCroatiaToolkit\Hnb\ExchangeRateClient;

$client = new ExchangeRateClient;

$client->rate('USD');           // today's USD rate, or null
$client->rate('USD', '2026-01-15'); // a specific date
$client->list();                // every currency for today
```

## IBAN

Generic ISO 13616 (MOD-97-10) checksum for any country, plus a
Croatia-specific shape check:

```php
use Stboris\LaravelCroatiaToolkit\Iban\Iban;

Iban::isValid('HR1523600001234567891');          // true — any valid IBAN
Iban::isValidCroatian('HR1523600001234567891');  // true — Croatian shape + checksum
```

As a validation rule:

```php
use Stboris\LaravelCroatiaToolkit\Iban\Rules\ValidIban;

$request->validate([
    'iban' => ['required', new ValidIban(croatianOnly: true)],
]);
```

## Testing

```bash
composer test
```

## License

MIT.
