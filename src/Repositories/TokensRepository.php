<?php

namespace Shetabit\Tokenable\Repositories;

use Illuminate\Database\Eloquent\Model;
use Shetabit\Tokenable\Models\Token;
use Shetabit\Tokenable\Abstracts\RepositoryAbstract;

class TokensRepository extends RepositoryAbstract
{
    public function model()
    {
        return Token::class;
    }

    /**
     * Retrieve a token.
     *
     * @return Token|null
     */
    public function findToken($token, ?string $type = null, ?Model $tokenable = null) : ?Token
    {
        return $this
            ->model
            ->where('token', '=', $token)
            ->when(!empty($type), function ($query) use ($type) {
                return $query->where('type', '=', $type);
            })
            ->when(!empty($tokenable), function($query) use ($tokenable) {
                return $query->whereHas('tokenable', $tokenable);
            })
            ->with('tokenable')
            ->first();
    }

    /**
     * Retrieve a token if it is valid.
     *
     * @return Token|null
     */
    public function findValidToken($token, ?string $type = null, ?Model $tokenable = null) : ?Token
    {
        return $this
            ->model
            ->valid()
            ->where('token', '=', $token)
            ->when(!empty($type), function ($query) use ($type) {
                return $query->where('type', '=', $type);
            })
            ->when(!empty($tokenable), function($query) use ($tokenable) {
                return $query->whereHas('tokenable', $tokenable);
            })
            ->with('tokenable')
            ->first();
    }
}
