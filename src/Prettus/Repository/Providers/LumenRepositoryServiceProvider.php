<?php

declare (strict_types=1);
namespace Prettus\Repository\Providers;

use Illuminate\Support\Service_Provider;
/**
 * Class LumenRepositoryServiceProvider
 * @package Prettus\Repository\Providers
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Lumen_Repository_Service_Provider extends Service_Provider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->commands(\Prettus\Repository\Generators\Commands\Repository_Command::class);
        $this->app->register(\Prettus\Repository\Providers\Event_Service_Provider::class);
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}