<?php

namespace PTS\Next2\Layer;

interface LayerFactoryInterface
{

    /**
     * @param callable[] $handlers
     */
    public function create(array $handlers, array $options = []): Layer;

    public function createFromConfig(array $config): Layer;
}
