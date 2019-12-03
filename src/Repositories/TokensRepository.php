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
     * @param $token
     * @param $type
     * @param $tokenable
     *
     * @return Token|null
     */
    public function findToken($token, ?string $type = null, ?Model $tokenable = null) : ?Token
    {
        if (!empty($tokenable)) {
            $query = $tokenable->temporaryTokens();
        } else {
            $query = $this->model;
        }

        return $query
            ->where('token', '=', $token)
            ->when(!empty($type), function ($query) use ($type) {
                return $query->where('type', '=', $type);
            })
            ->with('tokenable')
            ->first();
    }

    /**
     * Retrieve a token if it is valid.
     *
     * @param $token
     * @param $type
     * @param $tokenable
     *
     * @return Token|null
     */
    public function findValidToken($token, ?string $type = null, ?Model $tokenable = null) : ?Token
    {
        if (!empty($tokenable)) {
            $query = $tokenable->temporaryTokens();
        } else {
            $query = $this->model;
        }

        return $query
            ->valid()
            ->where('token', '=', $token)
            ->when(!empty($type), function ($query) use ($type) {
                return $query->where('type', '=', $type);
            })
            ->with('tokenable')
            ->first();
    }
}
