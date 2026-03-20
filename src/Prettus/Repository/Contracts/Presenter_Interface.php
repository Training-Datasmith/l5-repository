<?php

declare (strict_types=1);
namespace Prettus\Repository\Contracts;

/**
 * Interface PresenterInterface
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Presenter_Interface
{
    /**
     * Prepare data to present
     *
     * @param $data
     *
     * @return mixed
     */
    public function present($data);
}