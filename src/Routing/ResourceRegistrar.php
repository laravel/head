<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class ResourceRegistrar extends BaseResourceRegistrar
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function getResourceAction($resource, $controller, $method, $options)
    {
        $action = parent::getResourceAction($resource, $controller, $method, $options);

        foreach ([HeadDefinition::HEAD, HeadDefinition::GROUPS] as $key) {
            if (isset($options[$key])) {
                $action[$key] = $options[$key];
            }
        }

        return $action;
    }
}
