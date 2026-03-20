<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Prettus\Repository\Generators\File_Already_Exists_Exception;
use Prettus\Repository\Generators\Migration_Generator;
use Prettus\Repository\Generators\Model_Generator;
use Prettus\Repository\Generators\Repository_Eloquent_Generator;
use Prettus\Repository\Generators\Repository_Interface_Generator;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Class RepositoryCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Command extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:repository';
    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new repository.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';
    /**
     * @var Collection
     */
    protected $generators;
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
        $this->generators = new Collection();
        $migration_generator = new Migration_Generator(['name' => 'create_' . Str::snake(Str::plural($this->argument('name'))) . '_table', 'fields' => $this->option('fillable'), 'force' => $this->option('force')]);
        if (!$this->option('skip-migration')) {
            $this->generators->push($migration_generator);
        }
        $model_generator = new Model_Generator(['name' => $this->argument('name'), 'fillable' => $this->option('fillable'), 'force' => $this->option('force')]);
        if (!$this->option('skip-model')) {
            $this->generators->push($model_generator);
        }
        $this->generators->push(new Repository_Interface_Generator(['name' => $this->argument('name'), 'force' => $this->option('force')]));
        foreach ($this->generators as $generator) {
            $generator->run();
        }
        $model = $model_generator->get_root_namespace() . '\\' . $model_generator->get_name();
        $model = str_replace(['\\', '/'], '\\', $model);
        try {
            (new Repository_Eloquent_Generator(['name' => $this->argument('name'), 'rules' => $this->option('rules'), 'validator' => $this->option('validator'), 'force' => $this->option('force'), 'model' => $model]))->run();
            $this->info('Repository created successfully.');
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
        return [['name', Input_Argument::REQUIRED, 'The name of class being generated.', null]];
    }
    /**
     * The array of command options.
     *
     * @return array
     */
    public function get_options()
    {
        return [['fillable', null, Input_Option::VALUE_OPTIONAL, 'The fillable attributes.', null], ['rules', null, Input_Option::VALUE_OPTIONAL, 'The rules of validation attributes.', null], ['validator', null, Input_Option::VALUE_OPTIONAL, 'Adds validator reference to the repository.', null], ['force', 'f', Input_Option::VALUE_NONE, 'Force the creation if file already exists.', null], ['skip-migration', null, Input_Option::VALUE_NONE, 'Skip the creation of a migration file.', null], ['skip-model', null, Input_Option::VALUE_NONE, 'Skip the creation of a model.', null]];
    }
}