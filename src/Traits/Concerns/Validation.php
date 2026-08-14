<?php

namespace Shetabit\TokenBuilder\Traits\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Repositories\TokensRepository;

trait Validation
{
    /**
     * The token itself, when it is not generated but given.
     */
    abstract public function getUniqueId() : mixed;

    /**
     * The scope of the token.
     */
    abstract public function getType() : string|null;

    /**
     * The model the token belongs to.
     */
    abstract public function getRelatedItem() : Model|null;

    /**
     * The repository the tokens are read from and written to.
     */
    abstract protected function repository() : TokensRepository;

    /**
     * Retrieve a token if it is valid.
     */
    public function findValidToken() : Token|null
    {
        $token = $this->getUniqueId();

        if ($token === null || $token === '') {
            return null;
        }

        return $this->repository()->findValidToken($token, $this->getType(), $this->getRelatedItem());
    }

    /**
     * Retrieve every valid token of the related model, latest one first.
     *
     * The unique id is not part of this lookup: it answers which tokens the
     * related model (and the type, when one is set) still has, which is what
     * "may I hand out a new one?" asks, before any token is known.
     *
     * @return Collection<int, Token>
     */
    public function findValidTokens() : Collection
    {
        return $this->repository()->findValidTokens($this->getType(), $this->getRelatedItem());
    }

    /**
     * Determine if the related model has any valid token.
     *
     * As with findValidTokens(), the unique id is not part of this lookup.
     */
    public function hasAnyValidToken() : bool
    {
        return $this->repository()->hasAnyValidToken($this->getType(), $this->getRelatedItem());
    }

    /**
     * Determine if token is valid
     */
    public function isValid() : bool
    {
        return $this->findValidToken() instanceof Token;
    }

    /**
     * Determine if token is invalid
     */
    public function isInvalid() : bool
    {
        return !$this->isValid();
    }

    /**
     * Alias for invalid
     *
     * @alias isInvalid
     */
    public function isNotValid() : bool
    {
        return $this->isInvalid();
    }
}
