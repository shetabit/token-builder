<?php

namespace Shetabit\Tokenable;

use Illuminate\Database\Eloquent\Model;
use Shetabit\Tokenable\Repositories\TokensRepository;

class Token
{
    /**
     * Builder
     *
     * @var TokenBuilder
     */
    protected $builder;

    /**
     * Token constructor.
     *
     * @param TokenBuilder|null $tokenBuilder
     */
    public function __construct(TokenBuilder $tokenBuilder = null)
    {
        $tokenBuilder = $tokenBuilder ?? new TokenBuilder;

        $this->via($tokenBuilder);
    }

    /**
     * Set token's builder
     *
     * @param TokenBuilder $tokenBuilder
     */
    public function via(TokenBuilder $tokenBuilder)
    {
        $this->builder = $tokenBuilder;
    }

    /**
     * Create a new token
     *
     * @return mixed
     */
    public function build()
    {
        $builder = $this->builder;

        $defaultToken = random_int(10**(6-1)+1, (10**6)-1);

        $tokenData = [
            'token' => $builder->getToken() ?? $defaultToken,
            'expired_at' => $builder->getExpireDate(),
            'max_usage_limit' => $builder->getUsageLimit(),
            'type' => $builder->getType(),
        ];

        if ($this->getRelatedItem()) {
            $token = $this->getRelatedItem()->tokens()->create($tokenData);
        } else {
            $token = app(TokensRepository::class)->create($tokenData);
        }

        return $token;
    }

    /**
     * Alias build
     *
     * @alias build
     * @return Model
     */
    public function create()
    {
        return $this->build();
    }

    /**
     * Call builder methods
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(
            [
                $this->builder,
                $method,
            ],
            $arguments
        );
    }
}

