<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Migrations;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
/**
 * Class RulesParser
 * @package Prettus\Repository\Generators\Migrations
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Rules_Parser implements Arrayable
{
    /**
     * The set of rules.
     *
     * @var string
     */
    protected $rules;
    /**
     * Create new instance.
     *
     * @param string|null $rules
     */
    public function __construct($rules = null)
    {
        $this->rules = $rules;
    }
    /**
     * Convert string migration to array.
     *
     * @return array
     */
    public function to_array()
    {
        return $this->parse($this->rules);
    }
    /**
     * Parse a string to array of formatted rules.
     *
     * @param  string $rules
     */
    public function parse($rules): array
    {
        $this->rules = $rules;
        $parsed = [];
        foreach ($this->get_rules() as $rules_array) {
            $column = $this->get_column($rules_array);
            $attributes = $this->get_attributes($column, $rules_array);
            $parsed[$column] = $attributes;
        }
        return $parsed;
    }
    /**
     * Get array of rules.
     *
     * @return array
     */
    public function get_rules()
    {
        if (is_null($this->rules)) {
            return [];
        }
        return explode(',', str_replace(' ', '', $this->rules));
    }
    /**
     * Get column name from rules.
     *
     * @param  string $rules
     *
     * @return string
     */
    public function get_column($rules)
    {
        return Arr::first(explode('=>', $rules), function ($key, $value) {
            return $value;
        });
    }
    /**
     * Get column attributes.
     *
     * @param  string $rules
     * @return array
     */
    public function get_attributes(string $column, $rules): string
    {
        return str_replace($column . '=>', '', $rules);
    }
}