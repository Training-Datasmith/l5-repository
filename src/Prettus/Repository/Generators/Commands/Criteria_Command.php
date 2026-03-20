<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Prettus\Repository\Generators\Criteria_Generator;
use Prettus\Repository\Generators\File_Already_Exists_Exception;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Class CriteriaCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Criteria_Command extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:criteria';
    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new criteria.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Criteria';
    /**
     * Execute the command.
     *
     * @see fire()
     */
    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }
    /**
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {
        try {
            (new Criteria_Generator(['name' => $this->argument('name'), 'force' => $this->option('force')]))->run();
            $this->info('Criteria created successfully.');
        } catch (File_Already_Exists_Exception $ex) {
            $this->error($this->type . ' already exists!');
            return false;
        }
    }
    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function get_arguments()
    {
        return [['name', Input_Argument::REQUIRED, 'The name of class being generated.', null]];
    }
    /**
     * The array of command options.
     *
     * @return array
     */
    public function get_options()
    {
        return [['force', 'f', Input_Option::VALUE_NONE, 'Force the creation if file already exists.', null]];
    }
}