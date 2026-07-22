<?php

namespace Stboris\LaravelCroatiaToolkit\Oib\Exceptions;

use RuntimeException;
use Throwable;

class SudskiRegistarException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('Sudski registar client_id/client_secret are not configured. Register for free at https://sudreg-data-test.gov.hr (test) or https://sudreg-data.gov.hr (production).');
    }

    public static function connectionFailed(Throwable $previous): self
    {
        return new self('Could not connect to the Sudski registar (network timeout, DNS failure, or the service is unreachable).', previous: $previous);
    }

    public static function authenticationFailed(int $status): self
    {
        return new self("Sudski registar OAuth2 token request failed with HTTP {$status}.");
    }

    public static function requestFailed(int $status): self
    {
        return new self("Sudski registar lookup failed with HTTP {$status}.");
    }

    public static function notFound(string $oib): self
    {
        return new self("No subject found in Sudski registar for OIB {$oib}.");
    }
}
