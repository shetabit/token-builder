<?php

namespace Shetabit\TokenBuilder\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class TokenBuilder
 *
 * @package Shetabit\TokenBuilder\Facade
 * @see \Shetabit\TokenBuilder\Token
 */
class TokenBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'shetabit-token-builder';
    }
}
