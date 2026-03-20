<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Migrations;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
/**
 * Class SchemaParser
 * @package Prettus\Repository\Generators\Migrations
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Schema_Parser implements Arrayable
{
    /**
     * The array of custom attributes.
     *
     * @var array
     */
    protected $custom_attributes = ['remember_token' => 'rememberToken()', 'soft_delete' => 'softDeletes()'];
    /**
     * The migration schema.
     *
     * @var string
     */
    protected $schema;
    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($schema = null)
    {
        $this->schema = $schema;
    }
    /**
     * Render up migration fields.
     *
     * @return string
     */
    public function up()
    {
        return $this->render();
    }
    /**
     * Render the migration to formatted script.
     */
    public function render(): string
    {
        $results = '';
        foreach ($this->to_array() as $column => $attributes) {
            $results .= $this->create_field($column, $attributes);
        }
        return $results;
    }
    /**
     * Convert string migration to array.
     *
     * @return array
     */
    public function to_array()
    {
        return $this->parse($this->schema);
    }
    /**
     * Parse a string to array of formatted schema.
     *
     * @param  string $schema
     */
    public function parse($schema): array
    {
        $this->schema = $schema;
        $parsed = [];
        foreach ($this->get_schemas() as $schema_array) {
            $column = $this->get_column($schema_array);
            $attributes = $this->get_attributes($column, $schema_array);
            $parsed[$column] = $attributes;
        }
        return $parsed;
    }
    /**
     * Get array of schema.
     *
     * @return array
     */
    public function get_schemas()
    {
        if (is_null($this->schema)) {
            return [];
        }
        return explode(',', str_replace(' ', '', $this->schema));
    }
    /**
     * Get column name from schema.
     *
     * @param  string $schema
     *
     * @return string
     */
    public function get_column($schema)
    {
        return Arr::first(explode(':', $schema), function ($key, $value) {
            return $value;
        });
    }
    /**
     * Get column attributes.
     *
     * @param  string $schema
     * @return array
     */
    public function get_attributes(string $column, $schema)
    {
        $fields = str_replace($column . ':', '', $schema);
        return $this->has_custom_attribute($column) ? $this->get_custom_attribute($column) : explode(':', $fields);
    }
    /**
     * Determinte whether the given column is exist in customAttributes array.
     *
     * @param  string $column
     */
    public function has_custom_attribute($column): bool
    {
        return array_key_exists($column, $this->custom_attributes);
    }
    /**
     * Get custom attributes value.
     *
     * @param  string $column
     */
    public function get_custom_attribute($column): array
    {
        return (array) $this->custom_attributes[$column];
    }
    /**
     * Create field.
     *
     * @param  string $column
     * @param  array  $attributes
     */
    public function create_field($column, $attributes, $type = 'add'): string
    {
        $results = "\t\t\t" . '$table';
        foreach ($attributes as $key => $field) {
            $results .= $this->{"{$type}Column"}($key, $field, $column);
        }
        return $results .= ';' . PHP_EOL;
    }
    /**
     * Render down migration fields.
     */
    public function down(): string
    {
        $results = '';
        foreach ($this->to_array() as $column => $attributes) {
            $results .= $this->create_field($column, $attributes, 'remove');
        }
        return $results;
    }
    /**
     * Format field to script.
     *
     * @param  int    $key
     *
     */
    protected function add_column($key, string $field, string $column): string
    {
        if ($this->has_custom_attribute($column)) {
            return '->' . $field;
        }
        if ($key == 0) {
            return '->' . $field . "('" . $column . "')";
        }
        if (Str::contains($field, '(')) {
            return '->' . $field;
        }
        return '->' . $field . '()';
    }
    /**
     * Format field to script.
     *
     * @param  int    $key
     *
     */
    protected function remove_column($key, string $field, string $column): string
    {
        if ($this->has_custom_attribute($column)) {
            return '->' . $field;
        }
        return '->dropColumn(' . "'" . $column . "')";
    }
}