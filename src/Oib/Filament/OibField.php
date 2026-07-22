<?php

namespace Stboris\LaravelCroatiaToolkit\Oib\Filament;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Stboris\LaravelCroatiaToolkit\Oib\Data\CompanyData;
use Stboris\LaravelCroatiaToolkit\Oib\Exceptions\SudskiRegistarException;
use Stboris\LaravelCroatiaToolkit\Oib\Oib;
use Stboris\LaravelCroatiaToolkit\Oib\Rules\ValidOib;
use Stboris\LaravelCroatiaToolkit\Oib\SudskiRegistarClient;

/**
 * A text input that validates the OIB checksum and, once a valid OIB is
 * entered, autofills sibling fields from the Sudski registar - configure
 * the target field names with ->autofill().
 *
 * A magnifying-glass icon signals the lookup behaviour; it turns into a
 * checkmark on success or a warning triangle if the lookup failed. On
 * success, the field's lock flag (see lockKey()) is set to true - wire
 * the autofilled sibling fields' ->disabled() to it if you don't want
 * users overwriting official registry data by hand.
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
            ->helperText(trans('croatia-toolkit::fields.oib_helper'))
            ->suffixIcon(fn (Get $get) => match ($get($this->statusKey())) {
                'found' => 'heroicon-o-check-circle',
                'error' => 'heroicon-o-exclamation-triangle',
                default => 'heroicon-o-magnifying-glass',
            })
            ->suffixIconColor(fn (Get $get) => match ($get($this->statusKey())) {
                'found' => 'success',
                'error' => 'warning',
                default => 'gray',
            })
            ->afterStateUpdated(function (?string $state, callable $set): void {
                if (! Oib::isValid($state)) {
                    $set($this->statusKey(), null);
                    $set($this->lockKey(), false);

                    return;
                }

                try {
                    $company = app(SudskiRegistarClient::class)->lookup($state);
                } catch (SudskiRegistarException) {
                    $set($this->statusKey(), 'error');
                    $set($this->lockKey(), false);

                    return;
                }

                $set($this->statusKey(), 'found');
                $set($this->lockKey(), true);
                $this->fillSiblings($company, $set);
            });
    }

    public function autofill(?string $name = null, ?string $address = null): static
    {
        $this->autofillNameField = $name;
        $this->autofillAddressField = $address;

        return $this;
    }

    /**
     * State key that turns true once autofill succeeds - bind sibling
     * fields' ->disabled() to it, e.g.
     * TextInput::make('company_name')->disabled(fn (Get $get) => $get('oib_locked')).
     */
    public function lockKey(): string
    {
        return $this->getName().'_locked';
    }

    protected function statusKey(): string
    {
        return $this->getName().'_lookup_status';
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
