<?php

declare (strict_types=1);
namespace Prettus\Repository\Traits;

use Illuminate\Support\Arr;
use Prettus\Repository\Contracts\Presenter_Interface;
/**
 * Class PresentableTrait
 * @package Prettus\Repository\Traits
 * @author Anderson Andrade <contato@andersonandra.de>
 */
trait Presentable_Trait
{
    /**
     * @var PresenterInterface
     */
    protected $presenter;
    /**
     * @return $this
     */
    public function set_presenter(Presenter_Interface $presenter)
    {
        $this->presenter = $presenter;
        return $this;
    }
    /**
     * @param      $key
     *
     * @return mixed|null
     */
    public function present($key, $default = null)
    {
        if ($this->has_presenter()) {
            $data = $this->presenter()['data'];
            return Arr::get($data, $key, $default);
        }
        return $default;
    }
    protected function has_presenter(): bool
    {
        return isset($this->presenter) && $this->presenter instanceof Presenter_Interface;
    }
    /**
     * @return $this|mixed
     */
    public function presenter()
    {
        if ($this->has_presenter()) {
            return $this->presenter->present($this);
        }
        return $this;
    }
}