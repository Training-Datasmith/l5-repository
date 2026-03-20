<?php

declare (strict_types=1);
namespace Prettus\Repository\Providers;

use Illuminate\Support\Service_Provider;
/**
 * Class EventServiceProvider
 * @package Prettus\Repository\Providers
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Event_Service_Provider extends Service_Provider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [\Prettus\Repository\Events\Repository_Entity_Created::class => [\Prettus\Repository\Listeners\Clean_Cache_Repository::class], \Prettus\Repository\Events\Repository_Entity_Updated::class => [\Prettus\Repository\Listeners\Clean_Cache_Repository::class], \Prettus\Repository\Events\Repository_Entity_Deleted::class => [\Prettus\Repository\Listeners\Clean_Cache_Repository::class]];
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