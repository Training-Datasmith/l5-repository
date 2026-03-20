<?php

declare (strict_types=1);
namespace Prettus\Repository\Traits;

use Exception;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Prettus\Repository\Contracts\Criteria_Interface;
use Prettus\Repository\Helpers\Cache_Keys;
use Reflection_Object;
/**
 * Class CacheableRepository
 * @package Prettus\Repository\Traits
 * @author Anderson Andrade <contato@andersonandra.de>
 */
trait Cacheable_Repository
{
    /**
     * @var CacheRepository
     */
    protected $cache_repository;
    /**
     * Set Cache Repository
     *
     *
     * @return $this
     */
    public function set_cache_repository(Cache_Repository $repository)
    {
        $this->cache_repository = $repository;
        return $this;
    }
    /**
     * Return instance of Cache Repository
     *
     * @return CacheRepository
     */
    public function get_cache_repository()
    {
        if (is_null($this->cache_repository)) {
            $this->cache_repository = app(config('repository.cache.repository', 'cache'));
        }
        return $this->cache_repository;
    }
    /**
     * Skip Cache
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skip_cache($status = true)
    {
        $this->cache_skip = $status;
        return $this;
    }
    /**
     * @return bool
     */
    public function is_skipped_cache()
    {
        $skipped = $this->cache_skip ?? false;
        $request = app('Illuminate\Http\Request');
        $skip_cache_param = config('repository.cache.params.skipCache', 'skipCache');
        if ($request->has($skip_cache_param) && $request->get($skip_cache_param)) {
            return true;
        }
        return $skipped;
    }
    /**
     * @param $method
     *
     * @return bool
     */
    protected function allowed_cache($method)
    {
        $cache_enabled = config('repository.cache.enabled', true);
        if (!$cache_enabled) {
            return false;
        }
        $cache_only = $this->cache_only ?? config('repository.cache.allowed.only', null);
        $cache_except = $this->cache_except ?? config('repository.cache.allowed.except', null);
        if (is_array($cache_only)) {
            return in_array($method, $cache_only);
        }
        if (is_array($cache_except)) {
            return !in_array($method, $cache_except);
        }
        if (is_null($cache_only) && is_null($cache_except)) {
            return true;
        }
        return false;
    }
    /**
     * Get Cache key for the method
     *
     * @param $method
     * @param $args
     */
    public function get_cache_key($method, $args = null): string
    {
        $request = app('Illuminate\Http\Request');
        $args = serialize($args);
        $criteria = $this->serialize_criteria();
        $key = sprintf('%s@%s-%s', static::class, $method, md5($args . $criteria . $request->full_url()));
        Cache_Keys::put_key(static::class, $key);
        return $key;
    }
    /**
     * Serialize the criteria making sure the Closures are taken care of.
     */
    protected function serialize_criteria(): string
    {
        try {
            return serialize($this->get_criteria());
        } catch (Exception $e) {
            return serialize($this->get_criteria()->map(function ($criterion) {
                return $this->serialize_criterion($criterion);
            }));
        }
    }
    /**
     * Serialize single criterion with customized serialization of Closures.
     *
     * @param  \Prettus\Repository\Contracts\CriteriaInterface $criterion
     * @return \Prettus\Repository\Contracts\CriteriaInterface|array
     *
     * @throws \Exception
     */
    protected function serialize_criterion($criterion)
    {
        try {
            serialize($criterion);
            return $criterion;
        } catch (Exception $e) {
            // We want to take care of the closure serialization errors,
            // other than that we will simply re-throw the exception.
            if ($e->get_message() !== "Serialization of 'Closure' is not allowed") {
                throw $e;
            }
            $r = new Reflection_Object($criterion);
            return ['hash' => md5((string) $r)];
        }
    }
    /**
     * Get cache time
     *
     * Return minutes: version < 5.8
     * Return seconds: version >= 5.8
     *
     * @return int
     */
    public function get_cache_time()
    {
        $cache_minutes = $this->cache_minutes ?? config('repository.cache.minutes', 30);
        /**
         * https://laravel.com/docs/5.8/upgrade#cache-ttl-in-seconds
         */
        if ($this->version_compare($this->app->version(), '5.7.*', '>')) {
            return $cache_minutes * 60;
        }
        return $cache_minutes;
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
        if (!$this->allowed_cache('all') || $this->is_skipped_cache()) {
            return parent::all($columns);
        }
        $key = $this->get_cache_key('all', func_get_args());
        $time = $this->get_cache_time();
        $value = $this->get_cache_repository()->remember($key, $time, function () use ($columns) {
            return parent::all($columns);
        });
        $this->reset_model();
        $this->reset_scope();
        return $value;
    }
    /**
     * Retrieve all data of repository, paginated
     *
     * @param array $columns
     * @param string $method
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate')
    {
        if (!$this->allowed_cache('paginate') || $this->is_skipped_cache()) {
            return parent::paginate($limit, $columns, $method);
        }
        $key = $this->get_cache_key('paginate', func_get_args());
        $time = $this->get_cache_time();
        $value = $this->get_cache_repository()->remember($key, $time, function () use ($limit, $columns, $method) {
            return parent::paginate($limit, $columns, $method);
        });
        $this->reset_model();
        $this->reset_scope();
        return $value;
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
        if (!$this->allowed_cache('find') || $this->is_skipped_cache()) {
            return parent::find($id, $columns);
        }
        $key = $this->get_cache_key('find', func_get_args());
        $time = $this->get_cache_time();
        $value = $this->get_cache_repository()->remember($key, $time, function () use ($id, $columns) {
            return parent::find($id, $columns);
        });
        $this->reset_model();
        $this->reset_scope();
        return $value;
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
        if (!$this->allowed_cache('findByField') || $this->is_skipped_cache()) {
            return parent::find_by_field($field, $value, $columns);
        }
        $key = $this->get_cache_key('findByField', func_get_args());
        $time = $this->get_cache_time();
        $value = $this->get_cache_repository()->remember($key, $time, function () use ($field, $value, $columns) {
            return parent::find_by_field($field, $value, $columns);
        });
        $this->reset_model();
        $this->reset_scope();
        return $value;
    }
    /**
     * Find data by multiple fields
     *
     * @param array $columns
     * @return mixed
     */
    public function find_where(array $where, $columns = ['*'])
    {
        if (!$this->allowed_cache('findWhere') || $this->is_skipped_cache()) {
            return parent::find_where($where, $columns);
        }
        $key = $this->get_cache_key('findWhere', func_get_args());
        $time = $this->get_cache_time();
        $value = $this->get_cache_repository()->remember($key, $time, function () use ($where, $columns) {
            return parent::find_where($where, $columns);
        });
        $this->reset_model();
        $this->reset_scope();
        return $value;
    }
    /**
     * Find data by Criteria
     *
     *
     * @return mixed
     */
    public function get_by_criteria(Criteria_Interface $criteria)
    {
        if (!$this->allowed_cache('getByCriteria') || $this->is_skipped_cache()) {
            return parent::get_by_criteria($criteria);
        }
        $key = $this->get_cache_key('getByCriteria', func_get_args());
        $time = $this->get_cache_time();
        $value = $this->get_cache_repository()->remember($key, $time, function () use ($criteria) {
            return parent::get_by_criteria($criteria);
        });
        $this->reset_model();
        $this->reset_scope();
        return $value;
    }
}