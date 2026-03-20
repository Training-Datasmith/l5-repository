<?php

declare (strict_types=1);
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityDeleted
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Entity_Deleting extends Repository_Event_Base
{
    /**
     * @var string
     */
    protected $action = 'deleting';
}