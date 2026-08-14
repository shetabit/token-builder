<?php

namespace Shetabit\TokenBuilder\Abstracts;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Shetabit\TokenBuilder\Contracts\RepositoryInterface;

/**
 * @template TModel of Model
 */
abstract class RepositoryAbstract implements RepositoryInterface
{
    /**
     * The model this repository works with.
     *
     * @var TModel
     */
    protected Model $model;

    /**
     * RepositoryAbstract constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Retrieve repository's related model
     *
     * @return class-string<TModel>
     */
    abstract public function model() : string;

    /**
     * Retrieve fillable fields
     *
     * @return array<int, string>
     */
    public function getFillable() : array
    {
        return $this->model->getFillable();
    }

    /**
     * Create new item and store it
     *
     * @param array<string, mixed> $data
     *
     * @return TModel
     */
    public function create(array $data) : Model
    {
        /** @var TModel $created */
        $created = $this->model->newQuery()->create($data);

        return $created;
    }

    /**
     * Find item using its id.
     *
     * @return TModel|null
     */
    public function find(mixed $id) : Model|null
    {
        /** @var TModel|null $found */
        $found = $this->model->newQuery()->find($id);

        return $found;
    }

    /**
     * Update item using given data.
     *
     * @param TModel               $model
     * @param array<string, mixed> $data
     *
     * @return TModel
     */
    public function update(Model $model, array $data) : Model
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
     * @param TModel $model
     */
    public function delete(Model $model) : bool
    {
        return (bool) $model->delete();
    }

    /**
     * Instantiate model
     *
     * @return TModel
     *
     * @throws Exception
     */
    public function makeModel() : Model
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Model");
        }

        /** @var TModel $model */
        return $this->model = $model;
    }

    /**
     * Call model methods.
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments) : mixed
    {
        return $this->model->{$method}(...$arguments);
    }
}
