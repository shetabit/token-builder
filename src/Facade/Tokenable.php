<?php

namespace Shetabit\Tokenable\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class Tokenable
 *
 * @package Shetabit\Payment\Facade
 * @see \Shetabit\Tokenable\Token
 */
class Tokenable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'shetabit-tokenable';
    }
}
