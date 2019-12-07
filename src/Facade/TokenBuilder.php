<?php

namespace Shetabit\TokenBuilder\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class TokenBuilder
 *
 * @see \Shetabit\TokenBuilder\Builder
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
