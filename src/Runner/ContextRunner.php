<?php

declare(strict_types=1);

namespace PTS\Next2\Runner;

use PTS\Next2\Context\ContextInterface;
use PTS\Next2\Layer\Layer;
use RuntimeException;

class ContextRunner
{
    /**
     * @param Layer[] $layers
     */
    public function run(array $layers, ContextInterface $context, int $i): void
    {
        $layer = $this->getLayer($layers, $i);

        $handler = $layer->handler;
        $context->setCurrentLayer($layer);

        $handler($context, fn() => $this->run($layers, $context, $i + 1));
    }

    /**
     * @param Layer[] $layers
     */
    protected function getLayer(array $layers, int $i): Layer
    {
        return $layers[$i] ?? throw new RuntimeException("Can`t get layer by index - $i");
    }
}
