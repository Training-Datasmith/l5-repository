<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Migrations;

/**
 * Class NameParser
 * @package Prettus\Repository\Generators\Migrations
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Name_Parser
{
    /**
     * The migration name.
     *
     * @var string
     */
    protected $name;
    /**
     * The array data.
     *
     * @var array
     */
    protected $data = [];
    /**
     * The available schema actions.
     *
     * @var array
     */
    protected $actions = ['create' => ['create', 'make'], 'delete' => ['delete', 'remove'], 'add' => ['add', 'update', 'append', 'insert'], 'drop' => ['destroy', 'drop']];
    /**
     * The constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->data = $this->fetch_data();
    }
    /**
     * Fetch the migration name to an array data.
     */
    protected function fetch_data(): array
    {
        return explode('_', $this->name);
    }
    /**
     * Get original migration name.
     *
     * @return string
     */
    public function get_original_name()
    {
        return $this->name;
    }
    /**
     * Get table name.
     *
     * @return string
     */
    public function get_table()
    {
        return $this->get_table_name();
    }
    /**
     * Get the table will be used.
     *
     * @return string
     */
    public function get_table_name()
    {
        $matches = array_reverse($this->get_matches());
        return array_shift($matches);
    }
    /**
     * Get matches data from regex.
     *
     * @return array
     */
    public function get_matches()
    {
        preg_match($this->get_pattern(), $this->name, $matches);
        return $matches;
    }
    /**
     * Get name pattern.
     */
    public function get_pattern(): string
    {
        switch ($action = $this->get_action()) {
            case 'add':
            case 'append':
            case 'update':
            case 'insert':
                return "/{$action}_(.*)_to_(.*)_table/";
            case 'delete':
            case 'remove':
            case 'alter':
                return "/{$action}_(.*)_from_(.*)_table/";
            default:
                return "/{$action}_(.*)_table/";
        }
    }
    /**
     * Get schema type or action.
     *
     * @return string
     */
    public function get_action()
    {
        return head($this->data);
    }
    /**
     * Get the array data.
     *
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }
    /**
     * Determine whether the given type is same with the current schema action or type.
     *
     * @param $type
     */
    public function is($type): bool
    {
        return $type == $this->get_action();
    }
    /**
     * Determine whether the current schema action is a adding action.
     */
    public function is_add(): bool
    {
        return in_array($this->get_action(), $this->actions['add']);
    }
    /**
     * Determine whether the current schema action is a deleting action.
     */
    public function is_delete(): bool
    {
        return in_array($this->get_action(), $this->actions['delete']);
    }
    /**
     * Determine whether the current schema action is a creating action.
     */
    public function is_create(): bool
    {
        return in_array($this->get_action(), $this->actions['create']);
    }
    /**
     * Determine whether the current schema action is a dropping action.
     */
    public function is_drop(): bool
    {
        return in_array($this->get_action(), $this->actions['drop']);
    }
}