<?php

namespace Shetabit\Tokenable;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Shetabit\Tokenable\Repositories\TokensRepository;
use Shetabit\Tokenable\Traits\Concerns\Validation;

class TokenBuilder
{
    use Validation;

    protected $token;
    protected $expiredAt;
    protected $usageLimit;
    protected $type;
    protected $relatedItem;
    protected $data;

    /**
     * Tokens repository
     * 
     * @var TokensRepository
     */
    protected $tokensRepository;

    public function __construct()
    {
        $this->tokensRepository = app(TokensRepository::class);
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
        $this->token = $token;

        return $this;
    }

    /**
     * Retrieve token
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set data
     *
     * @param $token
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = is_array($data) ? $data : func_get_args();

        return $this;
    }

    /**
     * Retrieve data
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->data ?? [];
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
        $this->expiredAt = $date;

        return $this;
    }

    /**
     * Set expiration date
     *
     * @return mixed
     */
    public function getExpireDate()
    {
        return $this->expiredAt;
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
        $this->usageLimit = $number;

        return $this;
    }

    /**
     * Retrieve max usage count
     *
     * @return int
     */
    public function getUsageLimit() : int
    {
        return (int) $this->usageLimit;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * Retrieve type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
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
        $this->relatedItem = $item;

        return $this;
    }

    /**
     * Retrieve related Eloquent Model instance
     *
     * @return mixed
     */
    public function getRelatedItem()
    {
        return $this->relatedItem;
    }

    /**
     * Create a new token
     *
     * @return mixed
     */
    public function build()
    {
        $tokenData = [
            'token' => $this->getToken() ?? $this->generateRandomInt(),
            'expired_at' => $this->getExpireDate(),
            'max_usage_limit' => $this->getUsageLimit(),
            'type' => $this->getType(),
            'data' => $this->getData()
        ];

        if ($this->getRelatedItem()) {
            $token = $this->getRelatedItem()->temporaryTokens()->create($tokenData);
        } else {
            $token = $this->tokensRepository->create($tokenData);
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
    private function generateRandomInt($length = 6)
    {
        return random_int(10**($length-1)+1, (10**$length)-1);
    }
}

