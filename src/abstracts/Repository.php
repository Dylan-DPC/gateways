<?php

namespace faiverson\gateways\abstracts;

use faiverson\gateways\exceptions\RepositoryException;
use faiverson\gateways\contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Repository
 */
abstract class Repository implements RepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->app = app();
        $this->model = $this->app->make($this->model());
        if (!$this->model instanceof Model) {
            throw new RepositoryException(
                "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model"
            );
        }
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * @param array $columns
     * @return mixed
     */
    public function all(
        $data = null,
        $columns = ['*'],
        $limit = null,
        $offset = null,
        $order_by = null,
        $filters = [],
        $with = []
    ) {
        $query = $this->model;
        foreach ($with as $join) {
            $query = $query->with($join);
        }
        $query = $this->setFilters($query, $filters);
        if ($limit != null) {
            $query = $query->take($limit);
        }

        if ($offset != null) {
            $query = $query->skip($offset);
        }

        if ($order_by != null) {
            foreach ($order_by as $column => $dir) {
                $query = $query->orderBy($column, $dir);
            }
        }
        return $query->get($columns);
    }

    public function total($filters = array())
    {
        $query = $this->model;
        $query = $this->setFilters($query, $filters);
        return $query->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($this->setAttributes($data));
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate($this->setAttributes($data));
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function firstOrNew(array $data)
    {
        return $this->model->firstOrNew($this->setAttributes($data));
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function updateOrCreate(array $data, array $extra)
    {
        return $this->model->updateOrCreate($this->setAttributes($data), $this->setAttributes($extra));
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id)
    {
        return $this->model->find($id)->update($this->setAttributes($data));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'], $limit = null, $offset = null, $order_by = null, $with = [])
    {
        $query = $this->model;

        if (!empty($with) && count($with) > 0) {
            foreach ($with as $join) {
                $query = $query->with($join);
            }
        }

        if ($limit != null) {
            $query = $query->take($limit);
        }

        if ($offset != null) {
            $query = $query->skip($offset);
        }

        if ($order_by != null) {
            foreach ($order_by as $column => $dir) {
                $query = $query->orderBy($column, $dir);
            }
        }
        return $query->where($attribute, $value)->get($columns);
    }

    public function setAttributes(array $data)
    {
        $data = array_map(function ($value) {
            return is_array($value) || is_object($value) ? $this->setAttributes($value) : trim($value);
        }, $data);

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== '');
        });

        return $data;
    }

    public function setFilters($query, Array $filters)
    {
        return $query;
    }
}