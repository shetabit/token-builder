<?php

namespace Shetabit\TokenBuilder\Provider;

use Illuminate\Support\ServiceProvider;
use Shetabit\TokenBuilder\Builder;

class TokenBuilderServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Migrations that needs to be done by user.
         */
        $this->publishes(
            [
                __DIR__.'/../../database/migrations/' => database_path('migrations')
            ],
            'migrations'
        );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Bind to service container.
         */
        $this->app->bind('shetabit-token-builder', function () {
            return new Builder();
        });
    }
}
