<?php

declare (strict_types=1);
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityUpdated
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Entity_Updated extends Repository_Event_Base
{
    /**
     * @var string
     */
    protected $action = 'updated';
}