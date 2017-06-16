<?php

namespace faiverson\gateways\adapters\fractal;

use faiverson\gateways\adapters\fractal\abstracts\Fractable;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class Fractal implements Fractable
{
    /**
     * @var \League\Fractal\Manager
     */
    protected $manager;

    public function __construct(SerializerAbstract $serializer)
    {
        $this->manager = new Manager();
        $this->manager->setSerializer($serializer);
    }

    public function parseIncludes($includes)
    {
        $this->manager->parseIncludes($includes);
    }

    public function collection($data, TransformerAbstract $transformer = null, $resourceKey = null)
    {
        $resource = new Collection($data, $this->getTransformer($transformer), $resourceKey);
        return $this->manager->createData($resource)->toArray();
    }

    public function item($data, TransformerAbstract $transformer = null, $resourceKey = null)
    {
        $resource = new Item($data, $this->getTransformer($transformer), $resourceKey);
        return $this->manager->createData($resource)->toArray();
    }

    public function paginatedCollection(AbstractPaginator $paginator, TransformerAbstract $transformer = null, $resourceKey = null)
    {
        $resource = new Collection($paginator->getCollection(), $this->getTransformer($transformer), $resourceKey);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        return $this->manager->createData($resource)->toArray();
    }

    /**
     * @param TransformerAbstract $transformer
     * @return TransformerAbstract|callback
     */
    protected function getTransformer($transformer = null)
    {
        return $transformer ?: function($data) {

            if($data instanceof ArrayableInterface) {
                return $data->toArray();
            }

            return (array) $data;
        };
    }
}