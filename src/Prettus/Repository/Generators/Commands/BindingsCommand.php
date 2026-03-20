<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Commands;

use File;
use Illuminate\Console\Command;
use Prettus\Repository\Generators\Bindings_Generator;
use Prettus\Repository\Generators\File_Already_Exists_Exception;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Class BindingsCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Bindings_Command extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:bindings';
    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Add repository bindings to service provider.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Bindings';
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
            $binding_generator = new Bindings_Generator(['name' => $this->argument('name'), 'force' => $this->option('force')]);
            // generate repository service provider
            if (!file_exists($binding_generator->get_path())) {
                $this->call('make:provider', ['name' => $binding_generator->get_config_generator_class_path($binding_generator->get_path_config_node())]);
                // placeholder to mark the place in file where to prepend repository bindings
                $provider = File::get($binding_generator->get_path());
                File::put($binding_generator->get_path(), vsprintf(str_replace('//', '%s', $provider), ['//', $binding_generator->bind_placeholder]));
            }
            $binding_generator->run();
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