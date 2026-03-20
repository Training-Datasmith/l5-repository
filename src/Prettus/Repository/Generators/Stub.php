<?php

declare (strict_types=1);
namespace Prettus\Repository\Generators;

/**
 * Class Stub
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Stub
{
    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected static $base_path;
    /**
     * The stub path.
     *
     * @var string
     */
    protected $path;
    /**
     * The replacements array.
     *
     * @var array
     */
    protected $replaces = [];
    /**
     * The contructor.
     *
     * @param string $path
     */
    public function __construct($path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }
    /**
     * Create new self instance.
     *
     * @param  string $path
     *
     */
    public static function create($path, array $replaces = []): self
    {
        return new static($path, $replaces);
    }
    /**
     * Set base path.
     *
     * @param  string $path
     */
    public static function set_base_path($path): void
    {
        static::$base_path = $path;
    }
    /**
     * Set replacements array.
     *
     *
     * @return $this
     */
    public function replace(array $replaces = []): self
    {
        $this->replaces = $replaces;
        return $this;
    }
    /**
     * Get replacements.
     *
     * @return array
     */
    public function get_replaces()
    {
        return $this->replaces;
    }
    /**
     * Handle magic method __toString.
     */
    public function __toString(): string
    {
        return $this->render();
    }
    /**
     * Get stub contents.
     *
     * @return string
     */
    public function render()
    {
        return $this->get_contents();
    }
    /**
     * Get stub contents.
     *
     * @return mixed|string
     */
    public function get_contents()
    {
        $contents = file_get_contents($this->get_path());
        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$' . strtoupper($search) . '$', $replace, $contents);
        }
        return $contents;
    }
    /**
     * Get stub path.
     */
    public function get_path(): string
    {
        return static::$base_path . $this->path;
    }
    /**
     * Set stub path.
     *
     * @param string $path
     */
    public function set_path($path): self
    {
        $this->path = $path;
        return $this;
    }
}