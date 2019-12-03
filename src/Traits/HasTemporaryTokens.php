<?php

namespace Shetabit\Tokenable\Traits;

use Shetabit\Tokenable\Models\Token;
use Shetabit\Tokenable\TokenBuilder;

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
     * @return TokenBuilder
     */
    public function temporaryTokenFactory() : TokenBuilder
    {
        $tokenBuilder = new TokenBuilder;

        $tokenBuilder->setRelatedItem($this);

        return $tokenBuilder;
    }
}
