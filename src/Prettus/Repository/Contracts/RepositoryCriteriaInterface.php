<?php

declare (strict_types=1);
namespace Prettus\Repository\Contracts;

use Illuminate\Support\Collection;
/**
 * Interface RepositoryCriteriaInterface
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Repository_Criteria_Interface
{
    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return $this
     */
    public function push_criteria($criteria);
    /**
     * Pop Criteria
     *
     * @param $criteria
     *
     * @return $this
     */
    public function pop_criteria($criteria);
    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function get_criteria();
    /**
     * Find data by Criteria
     *
     *
     * @return mixed
     */
    public function get_by_criteria(Criteria_Interface $criteria);
    /**
     * Skip Criteria
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skip_criteria($status = true);
    /**
     * Reset all Criterias
     *
     * @return $this
     */
    public function reset_criteria();
}