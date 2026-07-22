<?php

use Stboris\LaravelCroatiaToolkit\Oib\Oib;

it('accepts a valid OIB', function () {
    expect(Oib::isValid('69435151530'))->toBeTrue();
});

it('rejects an OIB with a wrong check digit', function () {
    expect(Oib::isValid('69435151531'))->toBeFalse();
});

it('rejects OIBs of the wrong length', function () {
    expect(Oib::isValid('123'))->toBeFalse()
        ->and(Oib::isValid('694351515300'))->toBeFalse();
});

it('rejects non-numeric input', function () {
    expect(Oib::isValid('6943515153a'))->toBeFalse();
});

it('rejects null', function () {
    expect(Oib::isValid(null))->toBeFalse();
});

it('computes the check digit directly', function () {
    expect(Oib::checkDigit('6943515153'))->toBe(0);
});
