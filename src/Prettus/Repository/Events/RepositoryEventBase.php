<?php

declare (strict_types=1);
namespace Prettus\Repository\Events;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Repository_Interface;
/**
 * Class RepositoryEventBase
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
abstract class Repository_Event_Base
{
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    /**
     * @var string
     */
    protected $action;
    /**
     * @param Model               $model
     */
    public function __construct(Repository_Interface $repository, ?Model $model = null)
    {
        $this->repository = $repository;
        $this->model = $model;
    }
    /**
     * @return Model|array
     */
    public function get_model()
    {
        return $this->model;
    }
    /**
     * @return RepositoryInterface
     */
    public function get_repository()
    {
        return $this->repository;
    }
    /**
     * @return string
     */
    public function get_action()
    {
        return $this->action;
    }
}