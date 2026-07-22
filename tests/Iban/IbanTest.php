<?php

use Stboris\LaravelCroatiaToolkit\Iban\Iban;

it('accepts a valid Croatian IBAN', function () {
    expect(Iban::isValid('HR1523600001234567891'))->toBeTrue()
        ->and(Iban::isValidCroatian('HR1523600001234567891'))->toBeTrue();
});

it('accepts a valid IBAN written with spaces', function () {
    expect(Iban::isValid('HR15 2360 0001 2345 6789 1'))->toBeTrue();
});

it('rejects a wrong check digit', function () {
    expect(Iban::isValid('HR1423600001234567891'))->toBeFalse();
});

it('rejects the wrong length for a Croatian IBAN', function () {
    expect(Iban::isValidCroatian('HR152360000123456789'))->toBeFalse();
});

it('rejects a non-Croatian IBAN under the Croatian-specific check', function () {
    // Valid German IBAN (real-world published example).
    expect(Iban::isValid('DE89370400440532013000'))->toBeTrue()
        ->and(Iban::isValidCroatian('DE89370400440532013000'))->toBeFalse();
});

it('rejects garbage input', function () {
    expect(Iban::isValid('not-an-iban'))->toBeFalse();
});
