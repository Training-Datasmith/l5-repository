<?php

declare (strict_types=1);
namespace Prettus\Repository\Providers;

use Illuminate\Support\Service_Provider;
/**
 * Class RepositoryServiceProvider
 * @package Prettus\Repository\Providers
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Service_Provider extends Service_Provider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../../../resources/config/repository.php' => config_path('repository.php')]);
        $this->merge_config_from(__DIR__ . '/../../../resources/config/repository.php', 'repository');
        $this->load_translations_from(__DIR__ . '/../../../resources/lang', 'repository');
    }
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->commands(\Prettus\Repository\Generators\Commands\Repository_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Transformer_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Presenter_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Entity_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Validator_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Controller_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Bindings_Command::class);
        $this->commands(\Prettus\Repository\Generators\Commands\Criteria_Command::class);
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