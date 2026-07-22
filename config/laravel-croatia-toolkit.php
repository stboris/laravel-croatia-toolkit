<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sudski registar (court register) company lookup
    |--------------------------------------------------------------------------
    |
    | Free OAuth2 client-credentials access, registered separately for the
    | test and production environments at sudreg-data-test.gov.hr and
    | sudreg-data.gov.hr. No company registration required — a personal
    | email is enough to register and obtain a client id/secret.
    |
    */

    'sudski_registar' => [
        'base_url' => env('CROATIA_TOOLKIT_SUDREG_BASE_URL', 'https://sudreg-data-test.gov.hr'),
        'client_id' => env('CROATIA_TOOLKIT_SUDREG_CLIENT_ID'),
        'client_secret' => env('CROATIA_TOOLKIT_SUDREG_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HNB (Croatian National Bank) exchange rates
    |--------------------------------------------------------------------------
    |
    | Public, keyless API. Base currency is EUR since Croatia's 2023 changeover.
    |
    */

    'hnb' => [
        'base_url' => env('CROATIA_TOOLKIT_HNB_BASE_URL', 'https://api.hnb.hr/tecajn-eur/v3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => 5,
        'connect_timeout' => 3,
    ],

];
