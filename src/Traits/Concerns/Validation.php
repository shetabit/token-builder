<?php

namespace Shetabit\TokenBuilder\Traits\Concerns;

use Shetabit\TokenBuilder\Models\Token;

trait Validation
{
    /**
     * Retrieve a token if it is valid.
     *
     * @return Token|null
     */
    public function findValidToken() : ?Token
    {
        $token = $this->getUniqueId();
        $type = $this->getType();
        $tokenable = $this->getRelatedItem();

        return  empty($token) ? null : $this->tokensRepository->findValidToken($token, $type, $tokenable);
    }

    /**
     * Determine if token is valid
     * 
     * @return bool
     */
    public function isValid() : bool
    {
        return (bool) $this->findValidToken();
    }

    /**
     * Determine if token is invalid
     * 
     * @return bool
     */
    public function isInvalid() : bool
    {
        return !$this->isValid();
    }

    /**
     * Alias for invalid
     * 
     * @alias isInvalid
     * 
     * @return bool
     */
    public function isNotValid() : bool
    {
        return $this->isInvalid();
    }
}