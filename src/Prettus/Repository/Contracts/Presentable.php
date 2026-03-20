<?php

declare (strict_types=1);
namespace Prettus\Repository\Contracts;

/**
 * Interface Presentable
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Presentable
{
    /**
     * @return mixed
     */
    public function set_presenter(Presenter_Interface $presenter);
    /**
     * @return mixed
     */
    public function presenter();
}