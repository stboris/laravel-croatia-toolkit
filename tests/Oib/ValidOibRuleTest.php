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
