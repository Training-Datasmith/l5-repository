<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

/**
 * Class PresenterGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Presenter_Generator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'presenter/presenter';
    /**
     * Get root namespace.
     */
    public function get_root_namespace(): string
    {
        return parent::get_root_namespace() . parent::get_config_generator_class_path($this->get_path_config_node());
    }
    /**
     * Get generator path config node.
     */
    public function get_path_config_node(): string
    {
        return 'presenters';
    }
    /**
     * Get array replacements.
     */
    public function get_replacements(): array
    {
        $transformer_generator = new Transformer_Generator(['name' => $this->name]);
        $transformer = $transformer_generator->get_root_namespace() . '\\' . $transformer_generator->get_name() . 'Transformer';
        $transformer = str_replace(['\\', '/'], '\\', $transformer);
        echo $transformer;
        return array_merge(parent::get_replacements(), ['transformer' => $transformer]);
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '/' . $this->get_name() . 'Presenter.php';
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
}