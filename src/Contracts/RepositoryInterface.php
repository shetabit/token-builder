<?php

namespace Shetabit\TokenBuilder\Contracts;

interface RepositoryInterface
{
    /**
     * Retrieve repository's related model
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public function model() : string;
}
