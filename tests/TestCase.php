<?php

namespace Stboris\LaravelCroatiaToolkit\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Stboris\LaravelCroatiaToolkit\LaravelCroatiaToolkitServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelCroatiaToolkitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
