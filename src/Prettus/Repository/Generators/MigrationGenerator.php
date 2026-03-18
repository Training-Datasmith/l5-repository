<?php

declare(strict_types=1);

namespace Prettus\Repository\Generators;

use Prettus\Repository\Generators\Migrations\NameParser;
use Prettus\Repository\Generators\Migrations\SchemaParser;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class MigrationGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class MigrationGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'migration/plain';

    /**
     * Get base path of destination file.
     */
    public function getBasePath(): string
    {
        return base_path() . '/database/migrations/';
    }

    /**
     * Get destination path for generated file.
     */
    public function getPath(): string
    {
        return $this->getBasePath() . $this->getFileName() . '.php';
    }

    /**
     * Get generator path config node.
     */
    public function getPathConfigNode(): string
    {
        return '';
    }

    /**
     * Get root namespace.
     */
    public function getRootNamespace(): string
    {
        return '';
    }

    /**
     * Get migration name.
     */
    public function getMigrationName(): string
    {
        return strtolower($this->name);
    }

    /**
     * Get file name.
     */
    public function getFileName(): string
    {
        return date('Y_m_d_His_') . $this->getMigrationName();
    }

    /**
     * Get schema parser.
     */
    public function getSchemaParser(): \Prettus\Repository\Generators\Migrations\SchemaParser
    {
        return new SchemaParser($this->fields);
    }

    /**
     * Get name parser.
     */
    public function getNameParser(): \Prettus\Repository\Generators\Migrations\NameParser
    {
        return new NameParser($this->name);
    }

    /**
     * Get stub templates.
     *
     * @return string
     */
    public function getStub()
    {
        $parser = $this->getNameParser();

        $action = $parser->getAction();
        switch ($action) {
            case 'add':
            case 'append':
            case 'update':
            case 'insert':
                $file = 'change';
                $replacements = [
                    'class'       => $this->getClass(),
                    'table'       => $parser->getTable(),
                    'fields_up'   => $this->getSchemaParser()->up(),
                    'fields_down' => $this->getSchemaParser()->down(),
                ];
                break;

            case 'delete':
            case 'remove':
            case 'alter':
                $file = 'change';
                $replacements = [
                    'class'       => $this->getClass(),
                    'table'       => $parser->getTable(),
                    'fields_down' => $this->getSchemaParser()->up(),
                    'fields_up'   => $this->getSchemaParser()->down(),
                ];
                break;
            default:
                $file = 'create';
                $replacements = [
                    'class'  => $this->getClass(),
                    'table'  => $parser->getTable(),
                    'fields' => $this->getSchemaParser()->up(),
                ];
                break;
        }
        $path = config('repository.generator.stubsOverridePath', __DIR__);

        if (!file_exists($path . "/Stubs/migration/{$file}.stub")) {
            $path = __DIR__;
        }

        if (!file_exists($path . "/Stubs/migration/{$file}.stub")) {
            throw new FileNotFoundException($path . "/Stubs/migration/{$file}.stub");
        }

        return Stub::create($path . "/Stubs/migration/{$file}.stub", $replacements);
    }
}
