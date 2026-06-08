<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class ResourceRegistrar extends BaseResourceRegistrar
{
    public function __construct($router, protected RouteHeadRepository $repository)
    {
        parent::__construct($router);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function getResourceAction($resource, $controller, $method, $options)
    {
        $action = parent::getResourceAction($resource, $controller, $method, $options);

        return $this->repository->addResourceAction($action, $options);
    }
}
