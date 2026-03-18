<?php

declare(strict_types=1);

namespace Prettus\Repository\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * @package Prettus\Repository\Providers
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../../resources/config/repository.php' => config_path('repository.php'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../../resources/config/repository.php', 'repository');

        $this->loadTranslationsFrom(__DIR__ . '/../../../resources/lang', 'repository');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->commands(\Prettus\Repository\Generators\Commands\RepositoryCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\TransformerCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\PresenterCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\EntityCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\ValidatorCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\ControllerCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\BindingsCommand::class);
        $this->commands(\Prettus\Repository\Generators\Commands\CriteriaCommand::class);
        $this->app->register(\Prettus\Repository\Providers\EventServiceProvider::class);
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
