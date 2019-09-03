<?php
namespace Shetabit\Tokenable\Traits;

use Shetabit\Tokenable\Models\Token;

trait HasTemporaryTokens
{
    /**
     * Get all of the tokens.
     */
    public function temporaryTokens()
    {
        return $this->morphMany(Token::class, 'tokenable');
    }
}
