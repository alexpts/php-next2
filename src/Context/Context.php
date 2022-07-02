<?php

declare(strict_types=1);

namespace PTS\Next2\Context;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Layer\Layer;
use PTS\Next2\Layer\Resolver\LayerRequestResolver;
use PTS\Psr7\Response\JsonResponse;

class Context implements ContextInterface
{
    protected ResponseInterface $fallbackResponse;
    protected ContextRunner $runner;

    /**
     * @param Layer[] $allLayers
     */
    public function __construct(
        public ServerRequestInterface $request,
        array $allLayers = [],
        public ?ResponseInterface $response = null,
        LayerRequestResolver $resolver = null,
    ) {
        $this->fallbackResponse = new JsonResponse([ 'error' => 'Response was not created' ], 500);
        $this->runner = new ContextRunner($allLayers, $resolver ?? new LayerRequestResolver);
    }

    public function next(): void
    {
        $handler = $this->runner->getNextHandler($this->request);
        $handler($this);
        // return $handler - clear execution context / no simple - $ctx->next()($ctx)
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response ?? $this->fallbackResponse;
    }

    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function getCurrentLayer(): Layer
    {
        return $this->runner->layer;
    }

    /**
     * @return array<string, string>
     */
    public function getUriParams(): array
    {
        return $this->runner->uriParams;
    }
}