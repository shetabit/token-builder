<?php

namespace Shetabit\TokenBuilder\Provider;

use Illuminate\Support\ServiceProvider;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Facade\TokenBuilder;

class TokenBuilderServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot() : void
    {
        /**
         * Migrations that needs to be done by user.
         */
        $this->publishesMigrations(
            [
                dirname(__DIR__, 2).'/database/migrations' => database_path('migrations'),
            ],
            ['migrations', 'token-builder-migrations']
        );
    }

    /**
     * Register any package services.
     */
    public function register() : void
    {
        /**
         * Bind to service container.
         *
         * A builder is never shared: it carries the token that is being built.
         */
        $this->app->bind(TokenBuilder::SERVICE_NAME, static fn (): Builder => new Builder());

        $this->app->bind(Builder::class, static fn (): Builder => new Builder());
    }
}
