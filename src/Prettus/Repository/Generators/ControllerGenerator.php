<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

use Illuminate\Support\Str;
/**
 * Class ControllerGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Controller_Generator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'controller/controller';
    /**
     * Get root namespace.
     */
    public function get_root_namespace(): string
    {
        return str_replace('/', '\\', parent::get_root_namespace() . parent::get_config_generator_class_path($this->get_path_config_node()));
    }
    /**
     * Get generator path config node.
     */
    public function get_path_config_node(): string
    {
        return 'controllers';
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '/' . $this->get_controller_name() . 'Controller.php';
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
     * Gets controller name based on model
     */
    public function get_controller_name(): string
    {
        return ucfirst($this->get_plural_name());
    }
    /**
     * Gets plural name based on model
     *
     * @return string
     */
    public function get_plural_name()
    {
        return Str::plural(lcfirst(ucwords($this->get_class())));
    }
    /**
     * Get array replacements.
     */
    public function get_replacements(): array
    {
        return array_merge(parent::get_replacements(), ['controller' => $this->get_controller_name(), 'plural' => $this->get_plural_name(), 'singular' => $this->get_singular_name(), 'validator' => $this->get_validator(), 'repository' => $this->get_repository(), 'appname' => $this->get_app_namespace()]);
    }
    /**
     * Gets singular name based on model
     *
     * @return string
     */
    public function get_singular_name()
    {
        return Str::singular(lcfirst(ucwords($this->get_class())));
    }
    /**
     * Gets validator full class name
     */
    public function get_validator(): string
    {
        $validator_generator = new Validator_Generator(['name' => $this->name]);
        $validator = $validator_generator->get_root_namespace() . '\\' . $validator_generator->get_name();
        return 'use ' . str_replace(['\\', '/'], '\\', $validator) . 'Validator;';
    }
    /**
     * Gets repository full class name
     */
    public function get_repository(): string
    {
        $repository_generator = new Repository_Interface_Generator(['name' => $this->name]);
        $repository = $repository_generator->get_root_namespace() . '\\' . $repository_generator->get_name();
        return 'use ' . str_replace(['\\', '/'], '\\', $repository) . 'Repository;';
    }
}