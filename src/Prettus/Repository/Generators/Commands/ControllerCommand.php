<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Prettus\Repository\Generators\Controller_Generator;
use Prettus\Repository\Generators\File_Already_Exists_Exception;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Class ControllerCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Controller_Command extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:resource';
    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new RESTful controller.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';
    /**
     * ControllerCommand constructor.
     */
    public function __construct()
    {
        $this->name = (float) app()->version() >= 5.5 ? 'make:rest-controller' : 'make:resource';
        parent::__construct();
    }
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
            // Generate create request for controller
            $this->call('make:request', ['name' => $this->argument('name') . 'CreateRequest']);
            // Generate update request for controller
            $this->call('make:request', ['name' => $this->argument('name') . 'UpdateRequest']);
            (new Controller_Generator(['name' => $this->argument('name'), 'force' => $this->option('force')]))->run();
            $this->info($this->type . ' created successfully.');
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
        return [['name', Input_Argument::REQUIRED, 'The name of model for which the controller is being generated.', null]];
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