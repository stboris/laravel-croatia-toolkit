<?php

use Illuminate\Support\Facades\Validator;
use Stboris\LaravelCroatiaToolkit\Oib\Rules\ValidOib;

it('passes for a valid OIB', function () {
    $validator = Validator::make(['oib' => '69435151530'], ['oib' => [new ValidOib]]);

    expect($validator->passes())->toBeTrue();
});

it('fails for an invalid OIB', function () {
    $validator = Validator::make(['oib' => '69435151531'], ['oib' => [new ValidOib]]);

    expect($validator->fails())->toBeTrue();
});

it('translates the failure message to the app locale', function () {
    app()->setLocale('hr');

    $validator = Validator::make(['oib' => '69435151531'], ['oib' => [new ValidOib]]);

    expect($validator->errors()->first('oib'))->toBe('Polje oib nije ispravan OIB.');
});

it('defaults to English', function () {
    $validator = Validator::make(['oib' => '69435151531'], ['oib' => [new ValidOib]]);

    expect($validator->errors()->first('oib'))->toBe('The oib field is not a valid OIB.');
});
