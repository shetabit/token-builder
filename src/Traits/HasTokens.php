<?php
namespace Shetabit\Tokenable\Traits;

use Shetabit\Tokenable\Models\Token;

trait HasTokens
{
    /**
     * Get all of the tokens.
     */
    public function tokens()
    {
        return $this->morphMany(Token::class, 'tokenable');
    }
}
