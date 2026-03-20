<?php

declare (strict_types=1);
namespace Prettus\Repository\Contracts;

/**
 * Interface RepositoryInterface
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Repository_Interface
{
    /**
     * Retrieve data array for populate field select
     *
     * @param string $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function lists($column, $key = null);
    /**
     * Retrieve data array for populate field select
     * Compatible with Laravel 5.3
     * @param string $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null);
    /**
     * Sync relations
     *
     * @param $id
     * @param $relation
     * @param $attributes
     * @param bool $detaching
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true);
    /**
     * SyncWithoutDetaching
     *
     * @param $id
     * @param $relation
     * @param $attributes
     * @return mixed
     */
    public function sync_without_detaching($id, $relation, $attributes);
    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);
    /**
     * Retrieve all data of repository, paginated
     *
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*']);
    /**
     * Retrieve all data of repository, simple paginated
     *
     * @param array $columns
     * @return mixed
     */
    public function simple_paginate($limit = null, $columns = ['*']);
    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);
    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function find_by_field($field, $value, $columns = ['*']);
    /**
     * Find data by multiple fields
     *
     * @param array $columns
     * @return mixed
     */
    public function find_where(array $where, $columns = ['*']);
    /**
     * Find data by multiple values in one field
     *
     * @param       $field
     * @param array $columns
     * @return mixed
     */
    public function find_where_in($field, array $values, $columns = ['*']);
    /**
     * Find data by excluding multiple values in one field
     *
     * @param       $field
     * @param array $columns
     * @return mixed
     */
    public function find_where_not_in($field, array $values, $columns = ['*']);
    /**
     * Find data by between values in one field
     *
     * @param       $field
     * @param array $columns
     * @return mixed
     */
    public function find_where_between($field, array $values, $columns = ['*']);
    /**
     * Save a new entity in repository
     *
     *
     * @return mixed
     */
    public function create(array $attributes);
    /**
     * Update a entity in repository by id
     *
     * @param       $id
     * @return mixed
     */
    public function update(array $attributes, $id);
    /**
     * Update or Create an entity in repository
     *
     * @throws ValidatorException
     *
     *
     * @return mixed
     */
    public function update_or_create(array $attributes, array $values = []);
    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id);
    /**
     * Order collection by a given column
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function order_by($column, $direction = 'asc');
    /**
     * Load relations
     *
     * @param $relations
     *
     * @return $this
     */
    public function with($relations);
    /**
     * Load relation with closure
     *
     * @param string $relation
     * @param closure $closure
     *
     * @return $this
     */
    public function where_has($relation, $closure);
    /**
     * Add subselect queries to count the relations.
     *
     * @param  mixed $relations
     * @return $this
     */
    public function with_count($relations);
    /**
     * Set hidden fields
     *
     *
     * @return $this
     */
    public function hidden(array $fields);
    /**
     * Set visible fields
     *
     *
     * @return $this
     */
    public function visible(array $fields);
    /**
     * Query Scope
     *
     *
     * @return $this
     */
    public function scope_query(\Closure $scope);
    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function reset_scope();
    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function get_fields_searchable();
    /**
     * Set Presenter
     *
     * @param $presenter
     *
     * @return mixed
     */
    public function set_presenter($presenter);
    /**
     * Skip Presenter Wrapper
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skip_presenter($status = true);
    /**
     * Retrieve first data of repository, or return new Entity
     *
     *
     * @return mixed
     */
    public function first_or_new(array $attributes = []);
    /**
     * Retrieve first data of repository, or create new Entity
     *
     *
     * @return mixed
     */
    public function first_or_create(array $attributes = []);
    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments);
    /**
     * Trigger method calls to the model
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments);
}