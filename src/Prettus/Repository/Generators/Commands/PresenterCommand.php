<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Prettus\Repository\Generators\File_Already_Exists_Exception;
use Prettus\Repository\Generators\Presenter_Generator;
use Prettus\Repository\Generators\Transformer_Generator;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Class PresenterCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Presenter_Command extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:presenter';
    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new presenter.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Presenter';
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
            (new Presenter_Generator(['name' => $this->argument('name'), 'force' => $this->option('force')]))->run();
            $this->info('Presenter created successfully.');
            if (!\File::exists(app()->path() . '/Transformers/' . $this->argument('name') . 'Transformer.php')) {
                if ($this->confirm('Would you like to create a Transformer? [y|N]')) {
                    (new Transformer_Generator(['name' => $this->argument('name'), 'force' => $this->option('force')]))->run();
                    $this->info('Transformer created successfully.');
                }
            }
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
        return [['name', Input_Argument::REQUIRED, 'The name of model for which the presenter is being generated.', null]];
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