<?php
namespace Prettus\Repository\Generators;

use Prettus\Repository\Generators\Migrations\SchemaParser;

/**
 * Class RepositoryEloquentGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class RepositoryEloquentGenerator extends Generator
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
    public function getRootNamespace(): string
    {
        return parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode());
    }

    /**
     * Get generator path config node.
     */
    public function getPathConfigNode(): string
    {
        return 'repositories';
    }

    /**
     * Get destination path for generated file.
     */
    public function getPath(): string
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getName() . 'RepositoryEloquent.php';
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return config('repository.generator.basePath', app()->path());
    }

    /**
     * Get array replacements.
     */
    public function getReplacements(): array
    {
        $repository = parent::getRootNamespace() . parent::getConfigGeneratorClassPath('interfaces') . '\\' . $this->name . 'Repository;';
        $repository = str_replace([
            "\\",
            '/'
        ], '\\', $repository);

        return array_merge(parent::getReplacements(), [
            'fillable'      => $this->getFillable(),
            'use_validator' => $this->getValidatorUse(),
            'validator'     => $this->getValidatorMethod(),
            'repository'    => $repository,
            'model'         => $this->options['model'] ?? ''
        ]);
    }

    /**
     * Get the fillable attributes.
     */
    public function getFillable(): string
    {
        if (!$this->fillable) {
            return '[]';
        }
        $results = '[' . PHP_EOL;

        foreach ($this->getSchemaParser()->toArray() as $column => $value) {
            $results .= "\t\t'{$column}'," . PHP_EOL;
        }

        return $results . "\t" . ']';
    }

    /**
     * Get schema parser.
     */
    public function getSchemaParser(): \Prettus\Repository\Generators\Migrations\SchemaParser
    {
        return new SchemaParser($this->fillable);
    }

    public function getValidatorUse(): string
    {
        $validator = $this->getValidator();

        return "use {$validator};";
    }


    public function getValidator()
    {
        $validatorGenerator = new ValidatorGenerator([
            'name'  => $this->name,
            'rules' => $this->rules,
            'force' => $this->force,
        ]);

        $validator = $validatorGenerator->getRootNamespace() . '\\' . $validatorGenerator->getName();

        return str_replace([
            "\\",
            '/'
        ], '\\', $validator) . 'Validator';

    }


    public function getValidatorMethod(): string
    {
        if ($this->validator != 'yes') {
            return '';
        }

        $class = $this->getClass();

        return '/**' . PHP_EOL . '    * Specify Validator class name' . PHP_EOL . '    *' . PHP_EOL . '    * @return mixed' . PHP_EOL . '    */' . PHP_EOL . '    public function validator()' . PHP_EOL . '    {' . PHP_EOL . PHP_EOL . '        return ' . $class . 'Validator::class;' . PHP_EOL . '    }' . PHP_EOL;

    }
}
