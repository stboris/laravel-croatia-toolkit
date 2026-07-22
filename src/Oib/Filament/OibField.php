<?php

namespace Stboris\LaravelCroatiaToolkit\Oib\Filament;

use Filament\Forms\Components\TextInput;
use Stboris\LaravelCroatiaToolkit\Oib\Data\CompanyData;
use Stboris\LaravelCroatiaToolkit\Oib\Exceptions\SudskiRegistarException;
use Stboris\LaravelCroatiaToolkit\Oib\Oib;
use Stboris\LaravelCroatiaToolkit\Oib\Rules\ValidOib;
use Stboris\LaravelCroatiaToolkit\Oib\SudskiRegistarClient;

/**
 * A text input that validates the OIB checksum and, once a valid OIB is
 * entered, autofills sibling fields from the Sudski registar — configure
 * the target field names with ->autofill().
 */
class OibField extends TextInput
{
    protected ?string $autofillNameField = null;

    protected ?string $autofillAddressField = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('OIB')
            ->rule(new ValidOib)
            ->maxLength(11)
            ->live(onBlur: true)
            ->afterStateUpdated(function (?string $state, callable $set): void {
                if (! Oib::isValid($state)) {
                    return;
                }

                try {
                    $company = app(SudskiRegistarClient::class)->lookup($state);
                } catch (SudskiRegistarException) {
                    return;
                }

                $this->fillSiblings($company, $set);
            });
    }

    public function autofill(?string $name = null, ?string $address = null): static
    {
        $this->autofillNameField = $name;
        $this->autofillAddressField = $address;

        return $this;
    }

    protected function fillSiblings(CompanyData $company, callable $set): void
    {
        if ($this->autofillNameField) {
            $set($this->autofillNameField, $company->name);
        }

        if ($this->autofillAddressField && $company->address) {
            $set($this->autofillAddressField, $company->address);
        }
    }
}
