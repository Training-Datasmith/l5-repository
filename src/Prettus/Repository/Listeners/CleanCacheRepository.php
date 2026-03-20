<?php

declare (strict_types=1);
namespace Prettus\Repository\Listeners;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Contracts\Repository_Interface;
use Prettus\Repository\Events\Repository_Event_Base;
use Prettus\Repository\Helpers\Cache_Keys;
/**
 * Class CleanCacheRepository
 * @package Prettus\Repository\Listeners
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Clean_Cache_Repository
{
    /**
     * @var CacheRepository
     */
    protected $cache;
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var string
     */
    protected $action;
    /**
     *
     */
    public function __construct()
    {
        $this->cache = app(config('repository.cache.repository', 'cache'));
    }
    public function handle(Repository_Event_Base $event): void
    {
        try {
            $clean_enabled = config('repository.cache.clean.enabled', true);
            if ($clean_enabled) {
                $this->repository = $event->get_repository();
                $this->model = $event->get_model();
                $this->action = $event->get_action();
                if (config("repository.cache.clean.on.{$this->action}", true)) {
                    $cache_keys = Cache_Keys::get_keys(get_class($this->repository));
                    if (is_array($cache_keys)) {
                        foreach ($cache_keys as $key) {
                            $this->cache->forget($key);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error($e->get_message());
        }
    }
}