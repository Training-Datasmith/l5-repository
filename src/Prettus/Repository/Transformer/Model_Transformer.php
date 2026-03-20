<?php

declare (strict_types=1);
namespace Prettus\Repository\Transformer;

use League\Fractal\Transformer_Abstract;
use Prettus\Repository\Contracts\Transformable;
/**
 * Class ModelTransformer
 * @package Prettus\Repository\Transformer
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Model_Transformer extends Transformer_Abstract
{
    public function transform(Transformable $model)
    {
        return $model->transform();
    }
}