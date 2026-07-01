<?php

namespace Taha\Crudify\Tests;

use Taha\Crudify\ModelHelper;

class CrudifyServiceProviderTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertEquals('api', config('crudify.routes_prefix'));
        $this->assertIsArray(config('crudify.middlewares'));
    }

    public function test_model_helper_resolves_fqn(): void
    {
        $fqn = ModelHelper::getModelFqn('user', 'App');
        $this->assertEquals('App\\Models\\User', $fqn);
    }
}