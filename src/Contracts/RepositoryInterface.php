<?php

namespace Shetabit\Tokenable\Contracts;

interface RepositoryInterface
{
    /**
     * Retrieve repository's related model
     *
     * @return mixed
     */
    public function model();
}
