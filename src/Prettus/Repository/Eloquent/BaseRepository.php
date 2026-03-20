<?php

declare (strict_types=1);
namespace Prettus\Repository\Eloquent;

use Closure;
use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\Length_Aware_Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Prettus\Repository\Contracts\Criteria_Interface;
use Prettus\Repository\Contracts\Presentable;
use Prettus\Repository\Contracts\Presenter_Interface;
use Prettus\Repository\Contracts\Repository_Criteria_Interface;
use Prettus\Repository\Contracts\Repository_Interface;
use Prettus\Repository\Events\Repository_Entity_Created;
use Prettus\Repository\Events\Repository_Entity_Creating;
use Prettus\Repository\Events\Repository_Entity_Deleted;
use Prettus\Repository\Events\Repository_Entity_Deleting;
use Prettus\Repository\Events\Repository_Entity_Updated;
use Prettus\Repository\Events\Repository_Entity_Updating;
use Prettus\Repository\Exceptions\Repository_Exception;
use Prettus\Repository\Traits\Compares_Versions_Trait;
use Prettus\Validator\Contracts\Validator_Interface;
use Prettus\Validator\Exceptions\Validator_Exception;
/**
 * Class BaseRepository
 *
 * @package Prettus\Repository\Eloquent
 * @author  Anderson Andrade <contato@andersonandra.de>
 */
abstract class Base_Repository implements Repository_Interface, Repository_Criteria_Interface
{
    use Compares_Versions_Trait;
    /**
     * @var Application
     */
    protected $app;
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var array
     */
    protected $field_searchable = [];
    /**
     * @var PresenterInterface
     */
    protected $presenter;
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules;
    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria;
    /**
     * @var bool
     */
    protected $skip_criteria = false;
    /**
     * @var bool
     */
    protected $skip_presenter = false;
    /**
     * @var \Closure
     */
    protected $scope_query;
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->make_model();
        $this->make_presenter();
        $this->make_validator();
        $this->boot();
    }
    /**
     *
     */
    public function boot(): void
    {
    }
    /**
     * Returns the current Model instance
     *
     * @return Model
     */
    public function get_model()
    {
        return $this->model;
    }
    /**
     * @throws RepositoryException
     */
    public function reset_model(): void
    {
        $this->make_model();
    }
    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();
    /**
     * Specify Presenter class name
     *
     * @return string
     */
    public function presenter()
    {
        return null;
    }
    /**
     * Specify Validator class name of Prettus\Validator\Contracts\ValidatorInterface
     *
     * @throws Exception
     */
    public function validator()
    {
        if (isset($this->rules) && !is_null($this->rules) && is_array($this->rules) && !empty($this->rules)) {
            if (class_exists('Prettus\Validator\LaravelValidator')) {
                $validator = app('Prettus\Validator\LaravelValidator');
                if ($validator instanceof Validator_Interface) {
                    $validator->set_rules($this->rules);
                    return $validator;
                }
            } else {
                throw new Exception(trans('repository::packages.prettus_laravel_validation_required'));
            }
        }
        return null;
    }
    /**
     * Set Presenter
     *
     * @param $presenter
     *
     * @return $this
     */
    public function set_presenter($presenter)
    {
        $this->make_presenter($presenter);
        return $this;
    }
    /**
     * @return Model
     * @throws RepositoryException
     */
    public function make_model()
    {
        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new Repository_Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $model;
    }
    /**
     *
     * @return PresenterInterface
     * @throws RepositoryException
     */
    public function make_presenter($presenter = null)
    {
        $presenter = !is_null($presenter) ? $presenter : $this->presenter();
        if (!is_null($presenter)) {
            $this->presenter = is_string($presenter) ? $this->app->make($presenter) : $presenter;
            if (!$this->presenter instanceof Presenter_Interface) {
                throw new Repository_Exception("Class {$presenter} must be an instance of Prettus\\Repository\\Contracts\\PresenterInterface");
            }
            return $this->presenter;
        }
        return null;
    }
    /**
     *
     * @return null|ValidatorInterface
     * @throws RepositoryException
     */
    public function make_validator($validator = null)
    {
        $validator = !is_null($validator) ? $validator : $this->validator();
        if (!is_null($validator)) {
            $this->validator = is_string($validator) ? $this->app->make($validator) : $validator;
            if (!$this->validator instanceof Validator_Interface) {
                throw new Repository_Exception("Class {$validator} must be an instance of Prettus\\Validator\\Contracts\\ValidatorInterface");
            }
            return $this->validator;
        }
        return null;
    }
    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function get_fields_searchable()
    {
        return $this->field_searchable;
    }
    /**
     * Query Scope
     *
     *
     * @return $this
     */
    public function scope_query(\Closure $scope)
    {
        $this->scope_query = $scope;
        return $this;
    }
    /**
     * Retrieve data array for populate field select
     *
     * @param string      $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function lists($column, $key = null)
    {
        $this->apply_criteria();
        return $this->model->lists($column, $key);
    }
    /**
     * Retrieve data array for populate field select
     * Compatible with Laravel 5.3
     *
     * @param string      $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null)
    {
        $this->apply_criteria();
        return $this->model->pluck($column, $key);
    }
    /**
     * Sync relations
     *
     * @param      $id
     * @param      $relation
     * @param      $attributes
     * @param bool $detaching
     *
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true)
    {
        return $this->find($id)->{$relation}()->sync($attributes, $detaching);
    }
    /**
     * SyncWithoutDetaching
     *
     * @param $id
     * @param $relation
     * @param $attributes
     *
     * @return mixed
     */
    public function sync_without_detaching($id, $relation, $attributes)
    {
        return $this->sync($id, $relation, $attributes, false);
    }
    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }
        $this->reset_model();
        $this->reset_scope();
        return $this->parser_result($results);
    }
    /**
     * Count results of repository
     *
     * @param string $columns
     * @return int
     */
    public function count(array $where = [], $columns = '*')
    {
        $this->apply_criteria();
        $this->apply_scope();
        if ($where) {
            $this->apply_conditions($where);
        }
        $result = $this->model->count($columns);
        $this->reset_model();
        $this->reset_scope();
        return $result;
    }
    /**
     * Alias of All method
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function get($columns = ['*'])
    {
        return $this->all($columns);
    }
    /**
     * Retrieve first data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $results = $this->model->first($columns);
        $this->reset_model();
        return $this->parser_result($results);
    }
    /**
     * Retrieve first data of repository, or return new Entity
     *
     *
     * @return mixed
     */
    public function first_or_new(array $attributes = [])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $temporary_skip_presenter = $this->skip_presenter;
        $this->skip_presenter(true);
        $model = $this->model->first_or_new($attributes);
        $this->skip_presenter($temporary_skip_presenter);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Retrieve first data of repository, or create new Entity
     *
     *
     * @return mixed
     */
    public function first_or_create(array $attributes = [])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $temporary_skip_presenter = $this->skip_presenter;
        $this->skip_presenter(true);
        $model = $this->model->first_or_create($attributes);
        $this->skip_presenter($temporary_skip_presenter);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Retrieve data of repository with limit applied
     *
     * @param int   $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function limit($limit, $columns = ['*'])
    {
        // Shortcut to all with `limit` applied on query via `take`
        $this->take($limit);
        return $this->all($columns);
    }
    /**
     * Retrieve all data of repository, paginated
     *
     * @param null|int $limit
     * @param array    $columns
     * @param string   $method
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate')
    {
        $this->apply_criteria();
        $this->apply_scope();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends(app('request')->query());
        $this->reset_model();
        return $this->parser_result($results);
    }
    /**
     * Retrieve all data of repository, simple paginated
     *
     * @param null|int $limit
     * @param array    $columns
     *
     * @return mixed
     */
    public function simple_paginate($limit = null, $columns = ['*'])
    {
        return $this->paginate($limit, $columns, 'simplePaginate');
    }
    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $model = $this->model->find_or_fail($id, $columns);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function find_by_field($field, $value = null, $columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $model = $this->model->where($field, '=', $value)->get($columns);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Find data by multiple fields
     *
     * @param array $columns
     * @return mixed
     */
    public function find_where(array $where, $columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $this->apply_conditions($where);
        $model = $this->model->get($columns);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Find data by multiple values in one field
     *
     * @param       $field
     * @param array $columns
     * @return mixed
     */
    public function find_where_in($field, array $values, $columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $model = $this->model->where_in($field, $values)->get($columns);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Find data by excluding multiple values in one field
     *
     * @param       $field
     * @param array $columns
     * @return mixed
     */
    public function find_where_not_in($field, array $values, $columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $model = $this->model->where_not_in($field, $values)->get($columns);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Find data by between values in one field
     *
     * @param       $field
     * @param array $columns
     * @return mixed
     */
    public function find_where_between($field, array $values, $columns = ['*'])
    {
        $this->apply_criteria();
        $this->apply_scope();
        $model = $this->model->where_between($field, $values)->get($columns);
        $this->reset_model();
        return $this->parser_result($model);
    }
    /**
     * Save a new entity in repository
     *
     *
     * @return mixed
     * @throws ValidatorException
     *
     */
    public function create(array $attributes)
    {
        if (!is_null($this->validator)) {
            // we should pass data that has been casts by the model
            // to make sure data type are same because validator may need to use
            // this data to compare with data that fetch from database.
            if ($this->version_compare($this->app->version(), '5.2.*', '>')) {
                $attributes = $this->model->new_instance()->force_fill($attributes)->make_visible($this->model->get_hidden())->to_array();
            } else {
                $model = $this->model->new_instance()->force_fill($attributes);
                $model->make_visible($this->model->get_hidden());
                $attributes = $model->to_array();
            }
            $this->validator->with($attributes)->passes_or_fail(Validator_Interface::RULE_CREATE);
        }
        event(new Repository_Entity_Creating($this, $attributes));
        $model = $this->model->new_instance($attributes);
        $model->save();
        $this->reset_model();
        event(new Repository_Entity_Created($this, $model));
        return $this->parser_result($model);
    }
    /**
     * Update a entity in repository by id
     *
     * @param       $id
     *
     * @return mixed
     * @throws ValidatorException
     *
     */
    public function update(array $attributes, $id)
    {
        $this->apply_scope();
        if (!is_null($this->validator)) {
            // we should pass data that has been casts by the model
            // to make sure data type are same because validator may need to use
            // this data to compare with data that fetch from database.
            $model = $this->model->new_instance();
            $model->set_raw_attributes([]);
            $model->set_appends([]);
            if ($this->version_compare($this->app->version(), '5.2.*', '>')) {
                $attributes = $model->force_fill($attributes)->make_visible($this->model->get_hidden())->to_array();
            } else {
                $model->force_fill($attributes);
                $model->make_visible($this->model->get_hidden());
                $attributes = $model->to_array();
            }
            $this->validator->with($attributes)->set_id($id)->passes_or_fail(Validator_Interface::RULE_UPDATE);
        }
        $temporary_skip_presenter = $this->skip_presenter;
        $this->skip_presenter(true);
        $model = $this->model->find_or_fail($id);
        event(new Repository_Entity_Updating($this, $model));
        $model->fill($attributes);
        $model->save();
        $this->skip_presenter($temporary_skip_presenter);
        $this->reset_model();
        event(new Repository_Entity_Updated($this, $model));
        return $this->parser_result($model);
    }
    /**
     * Update or Create an entity in repository
     *
     *
     * @return mixed
     * @throws ValidatorException
     *
     */
    public function update_or_create(array $attributes, array $values = [])
    {
        $this->apply_scope();
        if (!is_null($this->validator)) {
            $this->validator->with(array_merge($attributes, $values))->passes_or_fail(Validator_Interface::RULE_CREATE);
        }
        $temporary_skip_presenter = $this->skip_presenter;
        $this->skip_presenter(true);
        event(new Repository_Entity_Creating($this, $attributes));
        $model = $this->model->update_or_create($attributes, $values);
        $this->skip_presenter($temporary_skip_presenter);
        $this->reset_model();
        event(new Repository_Entity_Updated($this, $model));
        return $this->parser_result($model);
    }
    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id)
    {
        $this->apply_scope();
        $temporary_skip_presenter = $this->skip_presenter;
        $this->skip_presenter(true);
        $model = $this->find($id);
        $original_model = clone $model;
        $this->skip_presenter($temporary_skip_presenter);
        $this->reset_model();
        event(new Repository_Entity_Deleting($this, $model));
        $deleted = $model->delete();
        event(new Repository_Entity_Deleted($this, $original_model));
        return $deleted;
    }
    /**
     * Delete multiple entities by given criteria.
     *
     *
     * @return int
     */
    public function delete_where(array $where)
    {
        $this->apply_scope();
        $temporary_skip_presenter = $this->skip_presenter;
        $this->skip_presenter(true);
        $this->apply_conditions($where);
        event(new Repository_Entity_Deleting($this, $this->model->get_model()));
        $deleted = $this->model->delete();
        event(new Repository_Entity_Deleted($this, $this->model->get_model()));
        $this->skip_presenter($temporary_skip_presenter);
        $this->reset_model();
        return $deleted;
    }
    /**
     * Check if entity has relation
     *
     * @param string $relation
     *
     * @return $this
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);
        return $this;
    }
    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }
    /**
     * Add subselect queries to count the relations.
     *
     * @param mixed $relations
     *
     * @return $this
     */
    public function with_count($relations)
    {
        $this->model = $this->model->with_count($relations);
        return $this;
    }
    /**
     * Load relation with closure
     *
     * @param string  $relation
     * @param closure $closure
     *
     * @return $this
     */
    public function where_has($relation, $closure)
    {
        $this->model = $this->model->where_has($relation, $closure);
        return $this;
    }
    /**
     * Set hidden fields
     *
     *
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model->set_hidden($fields);
        return $this;
    }
    /**
     * Set the "orderBy" value of the query.
     *
     * @param mixed  $column
     * @param string $direction
     *
     * @return $this
     */
    public function order_by($column, $direction = 'asc')
    {
        $this->model = $this->model->order_by($column, $direction);
        return $this;
    }
    /**
     * Set the "limit" value of the query.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function take($limit)
    {
        // Internally `take` is an alias to `limit`
        $this->model = $this->model->limit($limit);
        return $this;
    }
    /**
     * Set visible fields
     *
     *
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model->set_visible($fields);
        return $this;
    }
    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function push_criteria($criteria)
    {
        if (is_string($criteria)) {
            $criteria = new $criteria();
        }
        if (!$criteria instanceof Criteria_Interface) {
            throw new Repository_Exception('Class ' . get_class($criteria) . ' must be an instance of Prettus\Repository\Contracts\CriteriaInterface');
        }
        $this->criteria->push($criteria);
        return $this;
    }
    /**
     * Pop Criteria
     *
     * @param $criteria
     *
     * @return $this
     */
    public function pop_criteria($criteria)
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteria): bool {
            if (is_object($item) && is_string($criteria)) {
                return get_class($item) === $criteria;
            }
            if (is_string($item) && is_object($criteria)) {
                return $item === get_class($criteria);
            }
            return get_class($item) === get_class($criteria);
        });
        return $this;
    }
    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function get_criteria()
    {
        return $this->criteria;
    }
    /**
     * Find data by Criteria
     *
     *
     * @return mixed
     */
    public function get_by_criteria(Criteria_Interface $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        $results = $this->model->get();
        $this->reset_model();
        return $this->parser_result($results);
    }
    /**
     * Skip Criteria
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skip_criteria($status = true)
    {
        $this->skip_criteria = $status;
        return $this;
    }
    /**
     * Reset all Criterias
     *
     * @return $this
     */
    public function reset_criteria()
    {
        $this->criteria = new Collection();
        return $this;
    }
    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function reset_scope()
    {
        $this->scope_query = null;
        return $this;
    }
    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    protected function apply_scope()
    {
        if (isset($this->scope_query) && is_callable($this->scope_query)) {
            $callback = $this->scope_query;
            $this->model = $callback($this->model);
        }
        return $this;
    }
    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function apply_criteria()
    {
        if ($this->skip_criteria === true) {
            return $this;
        }
        $criteria = $this->get_criteria();
        if ($criteria) {
            foreach ($criteria as $c) {
                if ($c instanceof Criteria_Interface) {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }
        return $this;
    }
    /**
     * Applies the given where conditions to the model.
     *
     *
     * @return void
     */
    protected function apply_conditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $condition, $val] = $value;
                //smooth input
                $condition = preg_replace('/\s\s+/', ' ', trim($condition));
                //split to get operator, syntax: "DATE >", "DATE =", "DAY <"
                $operator = explode(' ', $condition);
                if (count($operator) > 1) {
                    $condition = $operator[0];
                    $operator = $operator[1];
                } else {
                    $operator = null;
                }
                switch (strtoupper($condition)) {
                    case 'IN':
                        if (!is_array($val)) {
                            throw new Repository_Exception("Input {$val} mus be an array");
                        }
                        $this->model = $this->model->where_in($field, $val);
                        break;
                    case 'NOTIN':
                        if (!is_array($val)) {
                            throw new Repository_Exception("Input {$val} mus be an array");
                        }
                        $this->model = $this->model->where_not_in($field, $val);
                        break;
                    case 'DATE':
                        if (!$operator) {
                            $operator = '=';
                        }
                        $this->model = $this->model->where_date($field, $operator, $val);
                        break;
                    case 'DAY':
                        if (!$operator) {
                            $operator = '=';
                        }
                        $this->model = $this->model->where_day($field, $operator, $val);
                        break;
                    case 'MONTH':
                        if (!$operator) {
                            $operator = '=';
                        }
                        $this->model = $this->model->where_month($field, $operator, $val);
                        break;
                    case 'YEAR':
                        if (!$operator) {
                            $operator = '=';
                        }
                        $this->model = $this->model->where_year($field, $operator, $val);
                        break;
                    case 'EXISTS':
                        if (!$val instanceof Closure) {
                            throw new Repository_Exception("Input {$val} must be closure function");
                        }
                        $this->model = $this->model->where_exists($val);
                        break;
                    case 'HAS':
                        if (!$val instanceof Closure) {
                            throw new Repository_Exception("Input {$val} must be closure function");
                        }
                        $this->model = $this->model->where_has($field, $val);
                        break;
                    case 'HASMORPH':
                        if (!$val instanceof Closure) {
                            throw new Repository_Exception("Input {$val} must be closure function");
                        }
                        $this->model = $this->model->where_has_morph($field, $val);
                        break;
                    case 'DOESNTHAVE':
                        if (!$val instanceof Closure) {
                            throw new Repository_Exception("Input {$val} must be closure function");
                        }
                        $this->model = $this->model->where_doesnt_have($field, $val);
                        break;
                    case 'DOESNTHAVEMORPH':
                        if (!$val instanceof Closure) {
                            throw new Repository_Exception("Input {$val} must be closure function");
                        }
                        $this->model = $this->model->where_doesnt_have_morph($field, $val);
                        break;
                    case 'BETWEEN':
                        if (!is_array($val)) {
                            throw new Repository_Exception("Input {$val} mus be an array");
                        }
                        $this->model = $this->model->where_between($field, $val);
                        break;
                    case 'BETWEENCOLUMNS':
                        if (!is_array($val)) {
                            throw new Repository_Exception("Input {$val} mus be an array");
                        }
                        $this->model = $this->model->where_between_columns($field, $val);
                        break;
                    case 'NOTBETWEEN':
                        if (!is_array($val)) {
                            throw new Repository_Exception("Input {$val} mus be an array");
                        }
                        $this->model = $this->model->where_not_between($field, $val);
                        break;
                    case 'NOTBETWEENCOLUMNS':
                        if (!is_array($val)) {
                            throw new Repository_Exception("Input {$val} mus be an array");
                        }
                        $this->model = $this->model->where_not_between_columns($field, $val);
                        break;
                    case 'RAW':
                        $this->model = $this->model->where_raw($val);
                        break;
                    default:
                        $this->model = $this->model->where($field, $condition, $val);
                }
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }
    /**
     * Skip Presenter Wrapper
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skip_presenter($status = true)
    {
        $this->skip_presenter = $status;
        return $this;
    }
    /**
     * Wrapper result data
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function parser_result($result)
    {
        if ($this->presenter instanceof Presenter_Interface) {
            if ($result instanceof Collection || $result instanceof Length_Aware_Paginator) {
                $result->each(function ($model) {
                    if ($model instanceof Presentable) {
                        $model->set_presenter($this->presenter);
                    }
                    return $model;
                });
            } elseif ($result instanceof Presentable) {
                $result = $result->set_presenter($this->presenter);
            }
            if (!$this->skip_presenter) {
                return $this->presenter->present($result);
            }
        }
        return $result;
    }
    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }
    /**
     * Trigger method calls to the model
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $this->apply_criteria();
        $this->apply_scope();
        return call_user_func_array([$this->model, $method], $arguments);
    }
}