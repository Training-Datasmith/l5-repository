<?php

declare (strict_types=1);
namespace Prettus\Repository\Helpers;

/**
 * Class CacheKeys
 * @package Prettus\Repository\Helpers
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Cache_Keys
{
    /**
     * @var string
     */
    protected static $store_file = 'repository-cache-keys.json';
    /**
     * @var array
     */
    protected static $keys;
    /**
     * @param $group
     * @param $key
     */
    public static function put_key($group, $key): void
    {
        self::load_keys();
        self::$keys[$group] = self::get_keys($group);
        if (!in_array($key, self::$keys[$group])) {
            self::$keys[$group][] = $key;
        }
        self::store_keys();
    }
    /**
     * @return array|mixed
     */
    public static function load_keys()
    {
        if (!is_null(self::$keys) && is_array(self::$keys)) {
            return self::$keys;
        }
        $file = self::get_file_keys();
        if (!file_exists($file)) {
            self::store_keys();
        }
        $content = file_get_contents($file);
        self::$keys = json_decode($content, true);
        return self::$keys;
    }
    /**
     * @return string
     */
    public static function get_file_keys()
    {
        return storage_path('framework/cache/' . self::$store_file);
    }
    /**
     * @return int
     */
    public static function store_keys()
    {
        $file = self::get_file_keys();
        self::$keys = is_null(self::$keys) ? [] : self::$keys;
        $content = json_encode(self::$keys);
        return file_put_contents($file, $content);
    }
    /**
     * @param $group
     *
     * @return array|mixed
     */
    public static function get_keys($group)
    {
        self::load_keys();
        self::$keys[$group] = self::$keys[$group] ?? [];
        return self::$keys[$group];
    }
    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        $instance = new static();
        return call_user_func_array([$instance, $method], $parameters);
    }
    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        $instance = new static();
        return call_user_func_array([$instance, $method], $parameters);
    }
}