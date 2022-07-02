<?php

declare(strict_types=1);

namespace PTS\Next2\Context;

use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Layer\Layer;
use PTS\Next2\Layer\Resolver\LayerRequestResolver;
use RuntimeException;

class ContextRunner
{
    public int $layerPos = 0;
    public int $handlerPos = 0;
    public ?Layer $layer = null;
    /** @var array<string, string> */
    public array $uriParams = [];

    /**
     * @param Layer[] $layers
     */
    public function __construct(
        public array $layers,
        public LayerRequestResolver $resolver,
    ) {
    }

    public function getNextHandler(ServerRequestInterface $request, bool $nextLayer = false): callable
    {
        if (!$this->layer || $nextLayer) {
            $this->layer = $this->nextLayer($request);
            return $this->layer->handlers[$this->handlerPos++];
        }

        $handler = $this->layer->handlers[$this->handlerPos++] ?? null;
        return $handler ?? $this->getNextHandler($request, true);
    }

    public function nextLayer(ServerRequestInterface $request): Layer
    {
        $this->handlerPos = 0;

        while ($layer = $this->layers[$this->layerPos++] ?? null) {
            if ($this->resolver->forRequest($layer, $request, true, $this->uriParams)) {
                return $layer;
            }
        }

        $message = sprintf("Can`t delegate to layer by index %d", $this->layerPos - 1);
        throw new RuntimeException($message);
    }
}