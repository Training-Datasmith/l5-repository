<?php
namespace Prettus\Repository\Presenter;

use Exception;
use Prettus\Repository\Transformer\ModelTransformer;

/**
 * Class ModelFractalPresenter
 * @package Prettus\Repository\Presenter
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class ModelFractalPresenter extends FractalPresenter
{

    /**
     * Transformer
     *
     * @throws Exception
     */
    public function getTransformer(): \Prettus\Repository\Transformer\ModelTransformer
    {
        if (!class_exists('League\Fractal\Manager')) {
            throw new Exception("Package required. Please install: 'composer require league/fractal' (0.12.*)");
        }

        return new ModelTransformer();
    }
}
