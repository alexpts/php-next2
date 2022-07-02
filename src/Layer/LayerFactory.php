<?php

declare(strict_types=1);

namespace PTS\Next2\Layer;

class LayerFactory implements LayerFactoryInterface
{
    protected Layer $layer;

    /**
     * @param callable[] $handlers
     */
    public function create(array $handlers, array $options = []): Layer
    {
        $layer = new Layer($handlers);

        foreach ($options as $name => $value) {
            match ($name) {
                'methods' => $layer->methods = explode('|', $value),
                default => property_exists($layer, $name) && $layer->{$name} = $value,
            };
        }

        return $layer;
    }

    public function createFromConfig(array $config): Layer
    {
        $handlers = $this->getHandler($config);
        unset($config['handlers']);
        return $this->create($handlers, $config);
    }

    /**
     * Any strategies for any cases via extend/overload this method
     * @return callable[]
     */
    protected function getHandler(array $params): array
    {
        return $params['handlers'];
    }
}
