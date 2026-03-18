<?php
namespace Prettus\Repository\Generators;

use Illuminate\Support\Str;

/**
 * Class ControllerGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class ControllerGenerator extends Generator
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
    public function getRootNamespace(): string
    {
        return str_replace('/', '\\', parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode()));
    }

    /**
     * Get generator path config node.
     */
    public function getPathConfigNode(): string
    {
        return 'controllers';
    }

    /**
     * Get destination path for generated file.
     */
    public function getPath(): string
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getControllerName() . 'Controller.php';
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
     * Gets controller name based on model
     */
    public function getControllerName(): string
    {

        return ucfirst($this->getPluralName());
    }

    /**
     * Gets plural name based on model
     *
     * @return string
     */
    public function getPluralName()
    {

        return Str::plural(lcfirst(ucwords($this->getClass())));
    }

    /**
     * Get array replacements.
     */
    public function getReplacements(): array
    {

        return array_merge(parent::getReplacements(), [
            'controller' => $this->getControllerName(),
            'plural'     => $this->getPluralName(),
            'singular'   => $this->getSingularName(),
            'validator'  => $this->getValidator(),
            'repository' => $this->getRepository(),
            'appname'    => $this->getAppNamespace(),
        ]);
    }

    /**
     * Gets singular name based on model
     *
     * @return string
     */
    public function getSingularName()
    {
        return Str::singular(lcfirst(ucwords($this->getClass())));
    }

    /**
     * Gets validator full class name
     */
    public function getValidator(): string
    {
        $validatorGenerator = new ValidatorGenerator([
            'name' => $this->name,
        ]);

        $validator = $validatorGenerator->getRootNamespace() . '\\' . $validatorGenerator->getName();

        return 'use ' . str_replace([
            "\\",
            '/'
        ], '\\', $validator) . 'Validator;';
    }


    /**
     * Gets repository full class name
     */
    public function getRepository(): string
    {
        $repositoryGenerator = new RepositoryInterfaceGenerator([
            'name' => $this->name,
        ]);

        $repository = $repositoryGenerator->getRootNamespace() . '\\' . $repositoryGenerator->getName();

        return 'use ' . str_replace([
            "\\",
            '/'
        ], '\\', $repository) . 'Repository;';
    }
}
