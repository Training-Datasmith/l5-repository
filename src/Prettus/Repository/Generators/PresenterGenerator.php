<?php
namespace Prettus\Repository\Generators;

/**
 * Class PresenterGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class PresenterGenerator extends Generator
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
    public function getRootNamespace(): string
    {
        return parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode());
    }

    /**
     * Get generator path config node.
     */
    public function getPathConfigNode(): string
    {
        return 'presenters';
    }

    /**
     * Get array replacements.
     */
    public function getReplacements(): array
    {
        $transformerGenerator = new TransformerGenerator([
            'name' => $this->name
        ]);
        $transformer = $transformerGenerator->getRootNamespace() . '\\' . $transformerGenerator->getName() . 'Transformer';
        $transformer = str_replace([
            "\\",
            '/'
        ], '\\', $transformer);
        echo $transformer;

        return array_merge(parent::getReplacements(), [
            'transformer' => $transformer
        ]);
    }

    /**
     * Get destination path for generated file.
     */
    public function getPath(): string
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getName() . 'Presenter.php';
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
}
