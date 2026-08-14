<?php

namespace Shetabit\TokenBuilder\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Models\Token;

/**
 * Gives a model tokens of its own.
 *
 * @phpstan-require-extends Model
 */
trait HasTemporaryTokens
{
    /**
     * Get all of the tokens.
     *
     * @return MorphMany<Token, $this>
     */
    public function temporaryTokens() : MorphMany
    {
        return $this->morphMany(Token::class, 'tokenable');
    }

    /**
     * Token builder factory method.
     */
    public function temporaryTokenBuilder() : Builder
    {
        return new Builder()->setRelatedItem($this);
    }
}
