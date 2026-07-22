<?php

namespace Stboris\LaravelCroatiaToolkit\Hnb\Exceptions;

use RuntimeException;

class HnbException extends RuntimeException
{
    public static function requestFailed(int $status): self
    {
        return new self("HNB exchange rate request failed with HTTP {$status}.");
    }
}
