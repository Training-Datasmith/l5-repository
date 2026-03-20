<?php

declare (strict_types=1);
namespace Prettus\Repository\Presenter;

use Exception;
use Prettus\Repository\Transformer\Model_Transformer;
/**
 * Class ModelFractalPresenter
 * @package Prettus\Repository\Presenter
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Model_Fractal_Presenter extends Fractal_Presenter
{
    /**
     * Transformer
     *
     * @throws Exception
     */
    public function get_transformer(): \Prettus\Repository\Transformer\Model_Transformer
    {
        if (!class_exists('League\Fractal\Manager')) {
            throw new Exception("Package required. Please install: 'composer require league/fractal' (0.12.*)");
        }
        return new Model_Transformer();
    }
}