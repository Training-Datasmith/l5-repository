<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

/**
 * Class BindingsGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Bindings_Generator extends Generator
{
    /**
     * The placeholder for repository bindings
     *
     * @var string
     */
    public $bind_placeholder = '//:end-bindings:';
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'bindings/bindings';
    public function run(): void
    {
        // Add entity repository binding to the repository service provider
        $provider = \File::get($this->get_path());
        $repository_interface = '\\' . $this->get_repository() . '::class';
        $repository_eloquent = '\\' . $this->get_eloquent_repository() . '::class';
        \File::put($this->get_path(), str_replace($this->bind_placeholder, "\$this->app->bind({$repository_interface}, {$repository_eloquent});" . PHP_EOL . '        ' . $this->bind_placeholder, $provider));
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/Providers/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '.php';
    }
    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function get_base_path()
    {
        return config('repository.generator.basePath', app()->path());
    }
    /**
     * Get generator path config node.
     */
    public function get_path_config_node(): string
    {
        return 'provider';
    }
    /**
     * Gets repository full class name
     *
     * @return string
     */
    public function get_repository()
    {
        $repository_generator = new Repository_Interface_Generator(['name' => $this->name]);
        $repository = $repository_generator->get_root_namespace() . '\\' . $repository_generator->get_name();
        return str_replace(['\\', '/'], '\\', $repository) . 'Repository';
    }
    /**
     * Gets eloquent repository full class name
     *
     * @return string
     */
    public function get_eloquent_repository()
    {
        $repository_generator = new Repository_Eloquent_Generator(['name' => $this->name]);
        $repository = $repository_generator->get_root_namespace() . '\\' . $repository_generator->get_name();
        return str_replace(['\\', '/'], '\\', $repository) . 'RepositoryEloquent';
    }
    /**
     * Get root namespace.
     */
    public function get_root_namespace(): string
    {
        return parent::get_root_namespace() . parent::get_config_generator_class_path($this->get_path_config_node());
    }
    /**
     * Get array replacements.
     */
    public function get_replacements(): array
    {
        return array_merge(parent::get_replacements(), ['repository' => $this->get_repository(), 'eloquent' => $this->get_eloquent_repository(), 'placeholder' => $this->bind_placeholder]);
    }
}