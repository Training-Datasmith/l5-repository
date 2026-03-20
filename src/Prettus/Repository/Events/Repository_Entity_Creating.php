<?php

declare (strict_types=1);
namespace Prettus\Repository\Events;

use Prettus\Repository\Contracts\Repository_Interface;
/**
 * Class RepositoryEntityCreated
 *
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Entity_Creating extends Repository_Event_Base
{
    /**
     * @var string
     */
    protected $action = 'creating';
    public function __construct(Repository_Interface $repository, array $model)
    {
        parent::__construct($repository);
        $this->model = $model;
    }
}