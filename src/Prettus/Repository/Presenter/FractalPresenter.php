<?php

declare (strict_types=1);
namespace Prettus\Repository\Presenter;

use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\Abstract_Paginator;
use Illuminate\Pagination\Length_Aware_Paginator;
use Illuminate\Pagination\Paginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\Illuminate_Paginator_Adapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\Serializer_Abstract;
use Prettus\Repository\Contracts\Presenter_Interface;
/**
 * Class FractalPresenter
 * @package Prettus\Repository\Presenter
 * @author Anderson Andrade <contato@andersonandra.de>
 */
abstract class Fractal_Presenter implements Presenter_Interface
{
    /**
     * @var string
     */
    protected $resource_key_item;
    /**
     * @var string
     */
    protected $resource_key_collection;
    /**
     * @var \League\Fractal\Manager
     */
    protected $fractal;
    /**
     * @var \League\Fractal\Resource\Collection
     */
    protected $resource;
    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (!class_exists('League\Fractal\Manager')) {
            throw new Exception(trans('repository::packages.league_fractal_required'));
        }
        $this->fractal = new Manager();
        $this->parse_includes();
        $this->setup_serializer();
    }
    /**
     * @return $this
     */
    protected function setup_serializer()
    {
        $serializer = $this->serializer();
        if ($serializer instanceof Serializer_Abstract) {
            $this->fractal->set_serializer(new $serializer());
        }
        return $this;
    }
    /**
     * @return $this
     */
    protected function parse_includes()
    {
        $request = app('Illuminate\Http\Request');
        $param_includes = config('repository.fractal.params.include', 'include');
        if ($request->has($param_includes)) {
            $this->fractal->parse_includes($request->get($param_includes));
        }
        return $this;
    }
    /**
     * Get Serializer
     *
     * @return SerializerAbstract
     */
    public function serializer()
    {
        $serializer = config('repository.fractal.serializer', 'League\Fractal\Serializer\DataArraySerializer');
        return new $serializer();
    }
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    abstract public function get_transformer();
    /**
     * Prepare data to present
     *
     * @param $data
     *
     * @return mixed
     * @throws Exception
     */
    public function present($data)
    {
        if (!class_exists('League\Fractal\Manager')) {
            throw new Exception(trans('repository::packages.league_fractal_required'));
        }
        if ($data instanceof Eloquent_Collection) {
            $this->resource = $this->transform_collection($data);
        } elseif ($data instanceof Abstract_Paginator) {
            $this->resource = $this->transform_paginator($data);
        } else {
            $this->resource = $this->transform_item($data);
        }
        return $this->fractal->create_data($this->resource)->to_array();
    }
    /**
     * @param $data
     *
     * @return Item
     */
    protected function transform_item($data)
    {
        return new Item($data, $this->get_transformer(), $this->resource_key_item);
    }
    /**
     * @param $data
     *
     * @return \League\Fractal\Resource\Collection
     */
    protected function transform_collection($data)
    {
        return new Collection($data, $this->get_transformer(), $this->resource_key_collection);
    }
    /**
     * @param AbstractPaginator|LengthAwarePaginator|Paginator $paginator
     *
     * @return \League\Fractal\Resource\Collection
     */
    protected function transform_paginator($paginator)
    {
        $collection = $paginator->get_collection();
        $resource = new Collection($collection, $this->get_transformer(), $this->resource_key_collection);
        $resource->set_paginator(new Illuminate_Paginator_Adapter($paginator));
        return $resource;
    }
}