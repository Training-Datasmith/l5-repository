<?php

declare (strict_types=1);
namespace Prettus\Repository\Contracts;

/**
 * Interface CriteriaInterface
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Criteria_Interface
{
    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     *
     * @return mixed
     */
    public function apply($model, Repository_Interface $repository);
}