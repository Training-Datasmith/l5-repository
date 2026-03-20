<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Prettus\Repository\Generators\File_Already_Exists_Exception;
use Prettus\Repository\Generators\Validator_Generator;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Class ValidatorCommand
 * @package Prettus\Repository\Generators\Commands
 */
class Validator_Command extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:validator';
    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new validator.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Validator';
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
            (new Validator_Generator(['name' => $this->argument('name'), 'rules' => $this->option('rules'), 'force' => $this->option('force')]))->run();
            $this->info('Validator created successfully.');
        } catch (File_Already_Exists_Exception $e) {
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
        return [['name', Input_Argument::REQUIRED, 'The name of model for which the validator is being generated.', null]];
    }
    /**
     * The array of command options.
     *
     * @return array
     */
    public function get_options()
    {
        return [['rules', null, Input_Option::VALUE_OPTIONAL, 'The rules of validation attributes.', null], ['force', 'f', Input_Option::VALUE_NONE, 'Force the creation if file already exists.', null]];
    }
}