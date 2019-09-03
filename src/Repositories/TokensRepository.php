<?php

namespace Shetabit\Tokenable\Repositories;

use Shetabit\Tokenable\Models\Token;
use Shetabit\Tokenable\Abstracts\RepositoryAbstract;

class TokensRepository extends RepositoryAbstract {
    public function model()
    {
        return Token::class;
    }

    public function getValidToken($token, $type = null)
    {
        return $this
            ->model
            ->valid()
            ->where('token', '=', $token)
            ->when(!empty($type), function($query) use ($type) {
                return $query->where('type', '=', $type);
            })
            ->with('tokenable')
            ->first();
    }
}
