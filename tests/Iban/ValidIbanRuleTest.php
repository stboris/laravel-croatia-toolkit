<?php

use Illuminate\Support\Facades\Validator;
use Stboris\LaravelCroatiaToolkit\Iban\Rules\ValidIban;

it('passes for any valid IBAN by default', function () {
    $validator = Validator::make(
        ['iban' => 'DE89370400440532013000'],
        ['iban' => [new ValidIban]],
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects a non-Croatian IBAN when restricted to Croatia', function () {
    $validator = Validator::make(
        ['iban' => 'DE89370400440532013000'],
        ['iban' => [new ValidIban(croatianOnly: true)]],
    );

    expect($validator->fails())->toBeTrue();
});

it('passes a Croatian IBAN when restricted to Croatia', function () {
    $validator = Validator::make(
        ['iban' => 'HR1523600001234567891'],
        ['iban' => [new ValidIban(croatianOnly: true)]],
    );

    expect($validator->passes())->toBeTrue();
});

it('translates the failure message to the app locale', function () {
    app()->setLocale('hr');

    $validator = Validator::make(['iban' => 'not-an-iban'], ['iban' => [new ValidIban]]);

    expect($validator->errors()->first('iban'))->toBe('Polje iban nije ispravan IBAN.');
});
