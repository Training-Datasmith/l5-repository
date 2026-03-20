<?php

declare (strict_types=1);
namespace Prettus\Repository\Traits;

/**
 * Class TransformableTrait
 * @package Prettus\Repository\Traits
 * @author Anderson Andrade <contato@andersonandra.de>
 */
trait Transformable_Trait
{
    /**
     * @return array
     */
    public function transform()
    {
        return $this->to_array();
    }
}