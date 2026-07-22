# Before this package is real — delete this file once done

## 0. Local test app

A DDEV sandbox app lives at `~/PhpStormProjects/laravel-croatia-toolkit-sandbox`
(not its own git repo — throwaway). It requires this package via a local path
repo, wired in through `.ddev/docker-compose.packages.yaml` (a bind mount,
since DDEV only mounts the project's own directory — a symlink to a sibling
folder does not resolve inside the container). Filament panel at
`https://laravel-croatia-toolkit-sandbox.ddev.site/admin`, login
`boris@example.test` / `password`, resource "Demo Companies" exercises
`OibField` + IBAN validation. Confirmed working end-to-end in a real browser
(valid/invalid OIB and IBAN both validate correctly, save round-trips to the
DB). Add your Sudski registar credentials to that app's `.env` once you have
them (step 1 below) to test real autofill — without them, the field
correctly no-ops instead of crashing.

## 1. Sudski registar (company lookup)

1. Register for free at **https://sudreg-data-test.gov.hr** (test environment) —
   just a valid email, no company/OIB needed. Pick "Javni korisnik" (public user)
   as the account type.
2. Verify the email, open the verification link again if needed — it shows your
   `Client ID` / `Client Secret`. Save both.
3. Add them to your app's `.env`:
   ```
   CROATIA_TOOLKIT_SUDREG_CLIENT_ID=...
   CROATIA_TOOLKIT_SUDREG_CLIENT_SECRET=...
   ```
4. Open the OpenAPI spec linked on that same verification page
   (`{base}/api/javni/dokumentacija/open_api`) and find the real endpoint for
   looking up one subject by OIB (the guide calls the method `detalji_subjekta`,
   but the literal REST path wasn't in the developer PDF I read).
5. Compare it against `lookupPath()` in `src/Oib/SudskiRegistarClient.php`
   (currently `/api/javni/subjekti/detalji_subjekta`, my best guess) and fix it
   if it's wrong.
6. Sanity check in tinker:
   ```php
   app(\Stboris\LaravelCroatiaToolkit\Oib\SudskiRegistarClient::class)->lookup('<a real test OIB>');
   ```
7. Check the response field names match what `CompanyData::fromSudskiRegistarResponse()`
   expects (`oib`, `mbs`, `tvrtka`/`naziv`, `adresa`, `pravni_oblik`, `status`) —
   adjust the mapping if the real API uses different keys.
8. Once it works in test, repeat registration at **https://sudreg-data.gov.hr**
   for production credentials (same free process).

## 2. Filament field

1. Install this package + Filament in a real app (or the `filament-outbox-pro`
   workbench app).
2. Drop `OibField::make('oib')->autofill(name: 'company_name', address: 'address')`
   into a form and confirm in the browser that typing a valid OIB actually
   fills the sibling fields.

## 3. HNB exchange rates

No registration needed (public API) — but worth a live sanity check once, since
this is the piece most likely to silently break if HNB ever restructures the
endpoint again:
```php
(new \Stboris\LaravelCroatiaToolkit\Hnb\ExchangeRateClient)->rate('USD');
```

## 4. IBAN

No action needed — pure offline checksum logic, already verified by hand.
Optionally spot-check against a couple of real bank IBANs you have on hand.

## 5. Before tagging v1.0.0

- [ ] Decide: public or private GitHub repo (currently pushed public)
- [ ] Packagist listing, if going public
- [ ] Double check `composer.json` `authors` / `keywords` / `description`
- [ ] Delete this file
