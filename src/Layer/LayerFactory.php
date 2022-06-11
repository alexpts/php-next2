<?php

declare(strict_types=1);

namespace PTS\Next2\Layer;

class LayerFactory implements LayerFactoryInterface
{

    public function create(callable|array $handler, array $options = [], ?string $path = null): Layer
    {
        $callable = is_array($handler) ? new GroupHandler($handler) : $handler;
        $layer = new Layer($callable, $path);

        foreach ($options as $name => $value) {
            if (property_exists($layer, $name)) {
                $layer->{$name} = $value;
            }
        }

        return $layer;
    }

    public function createFromConfig(array $config): Layer
    {
        $handler = $this->getHandler($config);
        return $this->create($handler, $config);
    }

    /**
     * Any strategies for any cases via extend/overload this method
     */
    protected function getHandler(array $params): callable
    {
        return $params['handler'];
    }
}
