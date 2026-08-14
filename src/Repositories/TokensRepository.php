<?php

namespace Shetabit\TokenBuilder\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        return $this->query($tokenable, $type)->where('token', '=', $token)->first();
    }

    /**
     * Retrieve a token if it is valid.
     */
    public function findValidToken(mixed $token, string|null $type = null, Model|null $tokenable = null) : Token|null
    {
        return $this->query($tokenable, $type)->where('token', '=', $token)->valid()->first();
    }

    /**
     * Retrieve every valid token, of the given model or of all of them.
     *
     * @return Collection<int, Token>
     */
    public function findValidTokens(string|null $type = null, Model|null $tokenable = null) : Collection
    {
        /** @var Collection<int, Token> $tokens */
        $tokens = $this->query($tokenable, $type)->valid()->latest()->get();

        return $tokens;
    }

    /**
     * Determine if there is any valid token, of the given model or of all of them.
     */
    public function hasAnyValidToken(string|null $type = null, Model|null $tokenable = null) : bool
    {
        return $this->query($tokenable, $type)->valid()->exists();
    }

    /**
     * The query that looks tokens up, of the given model or of all of them.
     *
     * @return Builder<Token>|MorphMany<Token, Model>
     */
    private function query(Model|null $tokenable, string|null $type) : Builder|MorphMany
    {
        if ($tokenable !== null && !method_exists($tokenable, 'temporaryTokens')) {
            throw new InvalidArgumentException(
                $tokenable::class.' has to use the '.HasTemporaryTokens::class.' trait to carry tokens.'
            );
        }

        $query = $tokenable === null ? $this->model->newQuery() : $tokenable->temporaryTokens();

        return $query
            ->when($type !== null && $type !== '', fn (Builder $query) => $query->where('type', '=', $type))
            ->with('tokenable');
    }
}
