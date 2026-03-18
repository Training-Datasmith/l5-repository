<?php
namespace Prettus\Repository\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventServiceProvider
 * @package Prettus\Repository\Providers
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Prettus\Repository\Events\RepositoryEntityCreated::class => [
            \Prettus\Repository\Listeners\CleanCacheRepository::class
        ],
        \Prettus\Repository\Events\RepositoryEntityUpdated::class => [
            \Prettus\Repository\Listeners\CleanCacheRepository::class
        ],
        \Prettus\Repository\Events\RepositoryEntityDeleted::class => [
            \Prettus\Repository\Listeners\CleanCacheRepository::class
        ]
    ];

    /**
     * Register the application's event listeners.
     */
    public function boot(): void
    {
        $events = app('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}
