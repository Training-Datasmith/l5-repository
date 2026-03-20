<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

use Prettus\Repository\Generators\Migrations\Rules_Parser;
use Prettus\Repository\Generators\Migrations\Schema_Parser;
/**
 * Class ValidatorGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Validator_Generator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'validator/validator';
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
        return 'validators';
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '/' . $this->get_name() . 'Validator.php';
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
        return array_merge(parent::get_replacements(), ['rules' => $this->get_rules()]);
    }
    /**
     * Get the rules.
     */
    public function get_rules(): string
    {
        if (!$this->rules) {
            return '[]';
        }
        $results = '[' . PHP_EOL;
        foreach ($this->get_schema_parser()->to_array() as $column => $value) {
            $results .= "\t\t'{$column}'\t=>'\t{$value}'," . PHP_EOL;
        }
        return $results . "\t" . ']';
    }
    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function get_schema_parser(): \Prettus\Repository\Generators\Migrations\Rules_Parser
    {
        return new Rules_Parser($this->rules);
    }
}