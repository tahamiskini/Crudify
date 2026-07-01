<?php

namespace Taha\Crudify\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Taha\Crudify\CrudifyServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [CrudifyServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('crudify', require __DIR__ . '/../config/crudify.php');
    }
}