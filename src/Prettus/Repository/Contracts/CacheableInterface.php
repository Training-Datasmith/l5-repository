<?php

declare (strict_types=1);
namespace Prettus\Repository\Contracts;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
/**
 * Interface CacheableInterface
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Cacheable_Interface
{
    /**
     * Set Cache Repository
     *
     *
     * @return $this
     */
    public function set_cache_repository(Cache_Repository $repository);
    /**
     * Return instance of Cache Repository
     *
     * @return CacheRepository
     */
    public function get_cache_repository();
    /**
     * Get Cache key for the method
     *
     * @param $method
     * @param $args
     *
     * @return string
     */
    public function get_cache_key($method, $args = null);
    /**
     * Get cache time
     *
     * @return int
     */
    public function get_cache_time();
    /**
     * Skip Cache
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skip_cache($status = true);
}