<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

use Prettus\Repository\Generators\Migrations\Schema_Parser;
/**
 * Class RepositoryEloquentGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Eloquent_Generator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'repository/eloquent';
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
        return 'repositories';
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . '/' . parent::get_config_generator_class_path($this->get_path_config_node(), true) . '/' . $this->get_name() . 'RepositoryEloquent.php';
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
        $repository = parent::get_root_namespace() . parent::get_config_generator_class_path('interfaces') . '\\' . $this->name . 'Repository;';
        $repository = str_replace(['\\', '/'], '\\', $repository);
        return array_merge(parent::get_replacements(), ['fillable' => $this->get_fillable(), 'use_validator' => $this->get_validator_use(), 'validator' => $this->get_validator_method(), 'repository' => $repository, 'model' => $this->options['model'] ?? '']);
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
    public function get_validator_use(): string
    {
        $validator = $this->get_validator();
        return "use {$validator};";
    }
    public function get_validator()
    {
        $validator_generator = new Validator_Generator(['name' => $this->name, 'rules' => $this->rules, 'force' => $this->force]);
        $validator = $validator_generator->get_root_namespace() . '\\' . $validator_generator->get_name();
        return str_replace(['\\', '/'], '\\', $validator) . 'Validator';
    }
    public function get_validator_method(): string
    {
        if ($this->validator != 'yes') {
            return '';
        }
        $class = $this->get_class();
        return '/**' . PHP_EOL . '    * Specify Validator class name' . PHP_EOL . '    *' . PHP_EOL . '    * @return mixed' . PHP_EOL . '    */' . PHP_EOL . '    public function validator()' . PHP_EOL . '    {' . PHP_EOL . PHP_EOL . '        return ' . $class . 'Validator::class;' . PHP_EOL . '    }' . PHP_EOL;
    }
}