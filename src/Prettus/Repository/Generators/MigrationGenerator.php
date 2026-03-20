<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

use Prettus\Repository\Generators\Migrations\Name_Parser;
use Prettus\Repository\Generators\Migrations\Schema_Parser;
use Symfony\Component\Http_Foundation\File\Exception\File_Not_Found_Exception;
/**
 * Class MigrationGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Migration_Generator extends Generator
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
    public function get_base_path(): string
    {
        return base_path() . '/database/migrations/';
    }
    /**
     * Get destination path for generated file.
     */
    public function get_path(): string
    {
        return $this->get_base_path() . $this->get_file_name() . '.php';
    }
    /**
     * Get generator path config node.
     */
    public function get_path_config_node(): string
    {
        return '';
    }
    /**
     * Get root namespace.
     */
    public function get_root_namespace(): string
    {
        return '';
    }
    /**
     * Get migration name.
     */
    public function get_migration_name(): string
    {
        return strtolower($this->name);
    }
    /**
     * Get file name.
     */
    public function get_file_name(): string
    {
        return date('Y_m_d_His_') . $this->get_migration_name();
    }
    /**
     * Get schema parser.
     */
    public function get_schema_parser(): \Prettus\Repository\Generators\Migrations\Schema_Parser
    {
        return new Schema_Parser($this->fields);
    }
    /**
     * Get name parser.
     */
    public function get_name_parser(): \Prettus\Repository\Generators\Migrations\Name_Parser
    {
        return new Name_Parser($this->name);
    }
    /**
     * Get stub templates.
     *
     * @return string
     */
    public function get_stub()
    {
        $parser = $this->get_name_parser();
        $action = $parser->get_action();
        switch ($action) {
            case 'add':
            case 'append':
            case 'update':
            case 'insert':
                $file = 'change';
                $replacements = ['class' => $this->get_class(), 'table' => $parser->get_table(), 'fields_up' => $this->get_schema_parser()->up(), 'fields_down' => $this->get_schema_parser()->down()];
                break;
            case 'delete':
            case 'remove':
            case 'alter':
                $file = 'change';
                $replacements = ['class' => $this->get_class(), 'table' => $parser->get_table(), 'fields_down' => $this->get_schema_parser()->up(), 'fields_up' => $this->get_schema_parser()->down()];
                break;
            default:
                $file = 'create';
                $replacements = ['class' => $this->get_class(), 'table' => $parser->get_table(), 'fields' => $this->get_schema_parser()->up()];
                break;
        }
        $path = config('repository.generator.stubsOverridePath', __DIR__);
        if (!file_exists($path . "/Stubs/migration/{$file}.stub")) {
            $path = __DIR__;
        }
        if (!file_exists($path . "/Stubs/migration/{$file}.stub")) {
            throw new File_Not_Found_Exception($path . "/Stubs/migration/{$file}.stub");
        }
        return Stub::create($path . "/Stubs/migration/{$file}.stub", $replacements);
    }
}