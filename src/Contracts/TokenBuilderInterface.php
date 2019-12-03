<?php

namespace Shetabit\Tokenable\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

interface TokenBuilderInterface
{
    /**
     * Set token
     *
     * @param $token
     *
     * @return $this
     */
    public function setToken($token);

    /**
     * Retrieve token
     *
     * @return mixed
     */
    public function getToken();

    /**
     * Set data
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data);

    /**
     * Retrieve data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set expiration date
     *
     * @param Carbon $date
     *
     * @return $this
     */
    public function setExpireDate(Carbon $date);

    /**
     * Set expiration date
     *
     * @return mixed
     */
    public function getExpireDate();

    /**
     * Set max usage count
     *
     * @param int $number
     *
     * @return $this
     */
    public function setUsageLimit(int $number);

    /**
     * Retrieve max usage count
     *
     * @return int
     */
    public function getUsageLimit() : int;

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type);

    /**
     * Retrieve type
     *
     * @return mixed
     */
    public function getType();

    /**
     * Set related Eloquent Model instance
     *
     * @param Model $item
     *
     * @return $this
     */
    public function setRelatedItem(Model $item);

    /**
     * Retrieve related Eloquent Model instance
     *
     * @return mixed
     */
    public function getRelatedItem();
}
