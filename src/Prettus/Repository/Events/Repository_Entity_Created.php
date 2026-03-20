<?php

declare (strict_types=1);
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityCreated
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Repository_Entity_Created extends Repository_Event_Base
{
    /**
     * @var string
     */
    protected $action = 'created';
}