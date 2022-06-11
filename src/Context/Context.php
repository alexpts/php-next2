<?php

declare(strict_types=1);

namespace PTS\Next2\Context;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Layer\Layer;
use PTS\Psr7\Response\JsonResponse;

class Context implements ContextInterface
{
    protected ResponseInterface $fallbackResponse;
    protected Layer $layer;

    public function __construct(
        public ServerRequestInterface $request,
        public ?ResponseInterface $response = null
    ) {
        $this->fallbackResponse = new JsonResponse([
            'error' => 'Response was not created'
        ], 500);
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response ?? $this->fallbackResponse;
    }

    public function getCurrentLayer(): Layer
    {
        return $this->layer;
    }

    public function setCurrentLayer(Layer $layer): void
    {
        $this->layer = $layer;
    }
}