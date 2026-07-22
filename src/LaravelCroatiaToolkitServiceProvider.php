<?php

namespace Stboris\LaravelCroatiaToolkit;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCroatiaToolkitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-croatia-toolkit')
            ->hasConfigFile('laravel-croatia-toolkit')
            ->hasTranslations();
    }
}
