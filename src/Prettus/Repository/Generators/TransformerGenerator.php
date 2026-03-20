<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

/**
 * Class TransformerGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Transformer_Generator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'transformer/transformer';
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
        return 'transformers';
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '/' . $this->get_name() . 'Transformer.php';
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
     * Get array replacements.
     */
    public function get_replacements(): array
    {
        $model_generator = new Model_Generator(['name' => $this->name]);
        $model = $model_generator->get_root_namespace() . '\\' . $model_generator->get_name();
        $model = str_replace(['\\', '/'], '\\', $model);
        return array_merge(parent::get_replacements(), ['model' => $model]);
    }
}