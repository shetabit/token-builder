<?php

namespace Shetabit\TokenBuilder\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Shetabit\TokenBuilder\Facade\TokenBuilder;
use Shetabit\TokenBuilder\Provider\TokenBuilderServiceProvider;
use Shetabit\TokenBuilder\Tests\Fixtures\User;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get the package's service providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app) : array
    {
        return [TokenBuilderServiceProvider::class];
    }

    /**
     * Get the package's facade aliases.
     *
     * @param  Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app) : array
    {
        return [
            'TokenBuilder' => TokenBuilder::class,
        ];
    }

    /**
     * Define the environment the tests run in.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app) : void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Run the migrations of the package and the ones of the test models.
     */
    protected function defineDatabaseMigrations() : void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/migrations');
    }

    /**
     * A saved user.
     */
    protected function createUser(string $name = 'Mahdi') : User
    {
        return User::query()->create(['name' => $name]);
    }
}
