<?php

namespace PTS\Next2\Layer;

interface LayerFactoryInterface
{
    /**
     * @param callable|callable[] $handler
     */
    public function create(callable|array $handler, array $options = []): Layer;

    public function createFromConfig(array $config): Layer;
}
