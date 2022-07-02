<?php

namespace PTS\Next2\Context;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Layer\Layer;

interface ContextInterface
{

    public function getRequest(): ServerRequestInterface;

    public function getResponse(): ResponseInterface;
    public function setResponse(ResponseInterface $response): static;

    public function getCurrentLayer(): Layer;

    /**
     * @return array<string, string>
     */
    public function getUriParams(): array;

    /**
     * Delegate request to next Layer
     */
    public function next(): void;
}