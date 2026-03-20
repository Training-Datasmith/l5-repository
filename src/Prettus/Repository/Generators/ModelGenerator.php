<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

use Prettus\Repository\Generators\Migrations\Schema_Parser;
/**
 * Class ModelGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Model_Generator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'model';
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
        return 'models';
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '/' . $this->get_name() . '.php';
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
        return array_merge(parent::get_replacements(), ['fillable' => $this->get_fillable()]);
    }
    /**
     * Get the fillable attributes.
     */
    public function get_fillable(): string
    {
        if (!$this->fillable) {
            return '[]';
        }
        $results = '[' . PHP_EOL;
        foreach ($this->get_schema_parser()->to_array() as $column => $value) {
            $results .= "\t\t'{$column}'," . PHP_EOL;
        }
        return $results . "\t" . ']';
    }
    /**
     * Get schema parser.
     */
    public function get_schema_parser(): \Prettus\Repository\Generators\Migrations\Schema_Parser
    {
        return new Schema_Parser($this->fillable);
    }
}