<?php

namespace Shetabit\TokenBuilder\Traits;

use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Builder;

trait HasTemporaryTokens
{
    /**
     * Get all of the tokens.
     */
    public function temporaryTokens()
    {
        return $this->morphMany(Token::class, 'tokenable');
    }

    /**
     * Token builder factory method.
     *
     * @return Builder
     */
    public function temporaryTokenBuilder() : Builder
    {
        $tokenBuilder = new Builder;

        $tokenBuilder->setRelatedItem($this);

        return $tokenBuilder;
    }
}
