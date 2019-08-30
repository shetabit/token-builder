<?php

namespace Shetabit\Payment\Provider;

use Shetabit\Tokenable\Token;
use Illuminate\Support\ServiceProvider;

class TokenableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
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
        $this->app->bind('shetabit-tokenable', function () {
            return new Token();
        });
    }
}
