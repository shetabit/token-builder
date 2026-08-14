<?php

namespace Shetabit\TokenBuilder\Traits;

use Illuminate\Database\Eloquent\Collection;
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
     * Get the tokens that have neither expired nor been used up, latest one
     * first, of the given type when one is given.
     *
     * @return Collection<int, Token>
     */
    public function validTemporaryTokens(string|null $type = null) : Collection
    {
        return $this->temporaryTokenBuilderOfType($type)->findValidTokens();
    }

    /**
     * Determine if there still is a valid token, of the given type when one is
     * given.
     */
    public function hasValidTemporaryToken(string|null $type = null) : bool
    {
        return $this->temporaryTokenBuilderOfType($type)->hasAnyValidToken();
    }

    /**
     * Token builder factory method.
     */
    public function temporaryTokenBuilder() : Builder
    {
        return new Builder()->setRelatedItem($this);
    }

    /**
     * A builder for this model, scoped to a type when one is given.
     */
    private function temporaryTokenBuilderOfType(string|null $type) : Builder
    {
        $builder = $this->temporaryTokenBuilder();

        return $type === null || $type === '' ? $builder : $builder->setType($type);
    }
}
