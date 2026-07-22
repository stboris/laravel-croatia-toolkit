# Before this package is real — delete this file once done

## 0. Local test app

A DDEV sandbox app lives at `~/PhpStormProjects/laravel-croatia-toolkit-sandbox`
(not its own git repo — throwaway). It requires this package via a local path
repo, wired in through `.ddev/docker-compose.packages.yaml` (a bind mount,
since DDEV only mounts the project's own directory — a symlink to a sibling
folder does not resolve inside the container). Filament panel at
`https://laravel-croatia-toolkit-sandbox.ddev.site/admin`, login
`boris@example.test` / `password`, resource "Demo Companies" exercises
`OibField` + IBAN validation. Its `.env` has real Sudski registar test
credentials already — gitignored, not committed anywhere.

## 1. Sudski registar (company lookup) — DONE, verified live 2026-07-22

Registered, fixed against the real OpenAPI spec, and confirmed end-to-end:
real OAuth2 token fetch, real company lookup (tested against Zagrebačka
banka d.d.), and the Filament `OibField` autofill working live in the
sandbox's browser UI. See `SudskiRegistarClient.php` and
`CompanyData.php` for the corrected contract.

**Still open:** production credentials. Repeat the same free registration
(personal email, no company needed) at **https://sudreg-data.gov.hr** when
ready to ship, and set `CROATIA_TOOLKIT_SUDREG_BASE_URL` +
production client id/secret wherever this package is actually deployed.

## 2. Filament field — DONE, verified live

Confirmed in the sandbox: valid OIB autofills company name + address from
real data; invalid OIB shows the (translated) validation error; missing
credentials fail silently instead of crashing the form.

## 3. HNB exchange rates — DONE, verified live 2026-07-22

Confirmed through the actual `ExchangeRateClient` class (not just curl):
`rate('USD')` returned a real current-day rate. Still the piece most
likely to silently break if HNB ever restructures the endpoint again —
worth an occasional re-check, no action needed now.

## 4. IBAN — DONE, verified live

Pure offline checksum logic, verified by hand and confirmed working in the
sandbox's Filament form (valid/invalid both render correctly, valid saves).

## 5. Translations — DONE

English (default) + Croatian validation messages, resolved via the app's
locale automatically. Verified in tests and live in the sandbox with
`APP_LOCALE=hr`.

## 6. Before tagging v1.0.0

- [ ] Decide: public or private GitHub repo (currently pushed public)
- [ ] Packagist listing, if going public
- [ ] Double check `composer.json` `authors` / `keywords` / `description`
- [ ] Production Sudski registar credentials (see #1)
- [ ] Delete this file
