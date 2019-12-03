<?php

namespace Shetabit\Tokenable\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Shetabit\Tokenable\Contracts\RepositoryInterface;

abstract class RepositoryAbstract implements RepositoryInterface
{
    protected $model;

    abstract function model();

    /**
     * RepositoryAbstract constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Retrieve fillable fields
     *
     * @return mixed
     */
    public function getFillable()
    {
        return $this->model->getFillable();
    }

    /**
     * Create new item and store it
     *
     * @param array $data
     * 
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Find item using its id.
     *
     * @param $id
     * 
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Update item using given data.
     *
     * @param $model
     * @param array $data
     * 
     * @return mixed
     */
    public function update($model, array $data)
    {
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }

        $model->save();

        return $model;
    }

    /**
     * Delete item.
     *
     * @param $model
     * 
     * @return mixed
     */
    public function delete($model)
    {
        return $model->delete();
    }

    /**
     * Instantiate model
     *
     * @return Model|\Illuminate\Foundation\Application|\Illuminate\Foundation\Auth\User|mixed
     * @throws \Exception
     */
    public function makeModel()
    {
        $model = app($this->model());

        if ($model instanceof Model || $model instanceof \Illuminate\Foundation\Auth\User) {
            return $this->model = $model;
        } else {
            throw new \Exception("Class {$this->model()} must be an instance of Model");
        }
    }

    /**
     * Call model methods.
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
                $this->model,
                $method,
            ],
            $arguments
        );
    }
}
