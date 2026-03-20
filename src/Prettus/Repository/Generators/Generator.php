<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
/**
 * Class Generator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
abstract class Generator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;
    /**
     * The array of options.
     *
     * @var array
     */
    protected $options;
    /**
     * The shortname of stub.
     *
     * @var string
     */
    protected $stub;
    /**
     * Create new instance of this class.
     */
    public function __construct(array $options = [])
    {
        $this->filesystem = new Filesystem();
        $this->options = $options;
    }
    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function get_filesystem()
    {
        return $this->filesystem;
    }
    /**
     * Set the filesystem instance.
     *
     *
     * @return $this
     */
    public function set_filesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }
    /**
     * Get stub template for generated file.
     *
     * @return string
     */
    public function get_stub()
    {
        $path = config('repository.generator.stubsOverridePath', __DIR__);
        if (!file_exists($path . '/Stubs/' . $this->stub . '.stub')) {
            $path = __DIR__;
        }
        return (new Stub($path . '/Stubs/' . $this->stub . '.stub', $this->get_replacements()))->render();
    }
    /**
     * Get template replacements.
     *
     * @return array
     */
    public function get_replacements()
    {
        return ['class' => $this->get_class(), 'namespace' => $this->get_namespace(), 'root_namespace' => $this->get_root_namespace()];
    }
    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function get_base_path()
    {
        return base_path();
    }
    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function get_path()
    {
        return $this->get_base_path() . '/' . $this->get_name() . '.php';
    }
    /**
     * Get name input.
     *
     * @return string
     */
    public function get_name()
    {
        $name = $this->name;
        if (Str::contains($this->name, '\\')) {
            $name = str_replace('\\', '/', $this->name);
        }
        if (Str::contains($this->name, '/')) {
            $name = str_replace('/', '/', $this->name);
        }
        return Str::studly(str_replace(' ', '/', ucwords(str_replace('/', ' ', $name))));
    }
    /**
     * Get application namespace
     *
     * @return string
     */
    public function get_app_namespace()
    {
        return \Illuminate\Container\Container::get_instance()->get_namespace();
    }
    /**
     * Get class name.
     *
     * @return string
     */
    public function get_class()
    {
        return Str::studly(class_basename($this->get_name()));
    }
    /**
     * Get paths of namespace.
     *
     * @return array
     */
    public function get_segments()
    {
        return explode('/', $this->get_name());
    }
    /**
     * Get root namespace.
     *
     * @return string
     */
    public function get_root_namespace()
    {
        return config('repository.generator.rootNamespace', $this->get_app_namespace());
    }
    /**
     * Get class-specific output paths.
     *
     * @param $class
     *
     * @return string
     */
    public function get_config_generator_class_path($class, $directory_path = false)
    {
        switch ($class) {
            case 'models' === $class:
                $path = config('repository.generator.paths.models', 'Entities');
                break;
            case 'repositories' === $class:
                $path = config('repository.generator.paths.repositories', 'Repositories');
                break;
            case 'interfaces' === $class:
                $path = config('repository.generator.paths.interfaces', 'Repositories');
                break;
            case 'presenters' === $class:
                $path = config('repository.generator.paths.presenters', 'Presenters');
                break;
            case 'transformers' === $class:
                $path = config('repository.generator.paths.transformers', 'Transformers');
                break;
            case 'validators' === $class:
                $path = config('repository.generator.paths.validators', 'Validators');
                break;
            case 'controllers' === $class:
                $path = config('repository.generator.paths.controllers', 'Http\Controllers');
                break;
            case 'provider' === $class:
                $path = config('repository.generator.paths.provider', 'RepositoryServiceProvider');
                break;
            case 'criteria' === $class:
                $path = config('repository.generator.paths.criteria', 'Criteria');
                break;
            default:
                $path = '';
        }
        if ($directory_path) {
            return str_replace('\\', '/', $path);
        }
        return str_replace('/', '\\', $path);
    }
    abstract public function get_path_config_node();
    /**
     * Get class namespace.
     *
     * @return string
     */
    public function get_namespace()
    {
        $segments = $this->get_segments();
        array_pop($segments);
        $root_namespace = $this->get_root_namespace();
        if ($root_namespace == false) {
            return null;
        }
        return 'namespace ' . rtrim($root_namespace . '\\' . implode('\\', $segments), '\\') . ';';
    }
    /**
     * Setup some hook.
     */
    public function set_up(): void
    {
    }
    /**
     * Run the generator.
     *
     * @return int
     * @throws FileAlreadyExistsException
     */
    public function run()
    {
        $this->set_up();
        if ($this->filesystem->exists($path = $this->get_path()) && !$this->force) {
            throw new File_Already_Exists_Exception($path);
        }
        if (!$this->filesystem->is_directory($dir = dirname($path))) {
            $this->filesystem->make_directory($dir, 0777, true, true);
        }
        return $this->filesystem->put($path, $this->get_stub());
    }
    /**
     * Get options.
     *
     * @return string
     */
    public function get_options()
    {
        return $this->options;
    }
    /**
     * Determinte whether the given key exist in options array.
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function has_option($key)
    {
        return array_key_exists($key, $this->options);
    }
    /**
     * Get value from options by given key.
     *
     * @param  string      $key
     * @param  string|null $default
     *
     * @return string
     */
    public function get_option($key, $default = null)
    {
        if (!$this->has_option($key)) {
            return $default;
        }
        return $this->options[$key] ?: $default;
    }
    /**
     * Helper method for "getOption".
     *
     * @param  string      $key
     * @param  string|null $default
     *
     * @return string
     */
    public function option($key, $default = null)
    {
        return $this->get_option($key, $default);
    }
    /**
     * Handle call to __get method.
     *
     *
     * @return string|mixed
     */
    public function __get(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }
        return $this->option($key);
    }
}