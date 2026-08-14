<?php

namespace Shetabit\TokenBuilder\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use InvalidArgumentException;
use Shetabit\TokenBuilder\Abstracts\RepositoryAbstract;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Traits\HasTemporaryTokens;

/**
 * @extends RepositoryAbstract<Token>
 */
class TokensRepository extends RepositoryAbstract
{
    /**
     * @return class-string<Token>
     */
    public function model() : string
    {
        return Token::class;
    }

    /**
     * Retrieve a token.
     */
    public function findToken(mixed $token, string|null $type = null, Model|null $tokenable = null) : Token|null
    {
        return $this->query($tokenable, $token, $type)->first();
    }

    /**
     * Retrieve a token if it is valid.
     */
    public function findValidToken(mixed $token, string|null $type = null, Model|null $tokenable = null) : Token|null
    {
        return $this->query($tokenable, $token, $type)->valid()->first();
    }

    /**
     * The query that looks a token up, of the given model or of all of them.
     *
     * @return Builder<Token>|MorphMany<Token, Model>
     */
    private function query(Model|null $tokenable, mixed $token, string|null $type) : Builder|MorphMany
    {
        if ($tokenable !== null && !method_exists($tokenable, 'temporaryTokens')) {
            throw new InvalidArgumentException(
                $tokenable::class.' has to use the '.HasTemporaryTokens::class.' trait to carry tokens.'
            );
        }

        $query = $tokenable === null ? $this->model->newQuery() : $tokenable->temporaryTokens();

        return $query
            ->where('token', '=', $token)
            ->when($type !== null && $type !== '', fn (Builder $query) => $query->where('type', '=', $type))
            ->with('tokenable');
    }
}
