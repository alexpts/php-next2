<?php

namespace PTS\Next2\Context;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Layer\Layer;

interface ContextInterface
{

    public function getRequest(): ServerRequestInterface;

    public function getResponse(): ResponseInterface;

    public function getCurrentLayer(): Layer;

    public function setCurrentLayer(Layer $layer): void;
}