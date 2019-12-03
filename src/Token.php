<?php

namespace Shetabit\Tokenable;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Shetabit\Tokenable\Contracts\TokenBuilderInterface;
use Shetabit\Tokenable\Repositories\TokensRepository;

class Token
{
    /**
     * Builder
     *
     * @var TokenBuilderInterface
     */
    protected $builder;

    /**
     * Token constructor.
     *
     * @param TokenBuilderInterface|null $tokenBuilder
     */
    public function __construct(TokenBuilderInterface $tokenBuilder = null)
    {
        $tokenBuilder = $tokenBuilder ?? new TokenBuilder;

        $this->setBuilder($tokenBuilder);
    }

    /**
     * Set token's builder
     *
     * @param TokenBuilderInterface $tokenBuilder
     */
    public function setBuilder(TokenBuilderInterface $tokenBuilder)
    {
        $this->builder = $tokenBuilder;

        return $this;
    }

    /**
     * Get token's builder
     * 
     * @return TokenBuilderInterface
     */
    public function getBuilder() : TokenBuilderInterface
    {
        return $this->builder;
    }

    /**
     * Set token
     *
     * @param $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->getBuilder()->setToken($token);

        return $this;
    }

    /**
     * Retrieve token
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->getBuilder()->getToken();
    }

    /**
     * Set data
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $data = is_array($data) ? $data : func_get_args();

        $this->getBuilder()->setData($data);

        return $this;
    }

    /**
     * Retrieve data
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->getBuilder()->getData();
    }

    /**
     * Set expiration date
     *
     * @param Carbon $date
     *
     * @return $this
     */
    public function setExpireDate(Carbon $date)
    {
        $this->getBuilder()->setExpireDate($date);

        return $this;
    }

    /**
     * Set expiration date
     *
     * @return mixed
     */
    public function getExpireDate()
    {
        return $this->getBuilder()->getExpireDate();
    }

    /**
     * Set max usage count
     *
     * @param int $number
     *
     * @return $this
     */
    public function setUsageLimit(int $number)
    {
        $this->getBuilder()->setUsageLimit($number);

        return $this;
    }

    /**
     * Retrieve max usage count
     *
     * @return int
     */
    public function getUsageLimit() : int
    {
        return $this->getBuilder()->getUsageLimit();
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->getBuilder()->setType($type);

        return $this;
    }

    /**
     * Retrieve type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->getBuilder()->getType();
    }

    /**
     * Set related Eloquent Model instance
     *
     * @param Model $item
     *
     * @return $this
     */
    public function setRelatedItem(Model $item)
    {
        $this->getBuilder()->setRelatedItem($item);

        return $this;
    }

    /**
     * Retrieve related Eloquent Model instance
     *
     * @return mixed
     */
    public function getRelatedItem()
    {
        return $this->getBuilder()->getRelatedItem();
    }

    /**
     * Create a new token
     *
     * @return mixed
     */
    public function build()
    {
        $builder = $this->getBuilder();

        $tokenData = [
            'token' => $builder->getToken() ?? $this->generateToken(),
            'expired_at' => $builder->getExpireDate(),
            'max_usage_limit' => $builder->getUsageLimit(),
            'type' => $builder->getType(),
            'data' => $builder->getData();
        ];

        if ($this->getRelatedItem()) {
            $token = $this->getRelatedItem()->temporaryTokens()->create($tokenData);
        } else {
            $token = app(TokensRepository::class)->create($tokenData);
        }

        return $token;
    }

    /**
     * Alias build
     *
     * @alias build
     *
     * @return Model
     */
    public function create()
    {
        return $this->build();
    }

    /**
     * Generate a random token
     * 
     * @return int
     */
    private function generateToken($length = 6)
    {
        return random_int(10**($length-1)+1, (10**$length)-1);
    }

    /**
     * Call builder methods
     *
     * @param $method
     * @param $arguments
     *
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
