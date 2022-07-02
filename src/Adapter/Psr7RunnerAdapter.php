<?php

declare(strict_types=1);

namespace PTS\Next2\Adapter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Context\Context;
use PTS\Next2\Layer\Layer;
use PTS\Next2\Layer\Resolver\LayerResolverInterface;

class Psr7RunnerAdapter
{
    public function __construct(
        protected LayerResolverInterface $resolver
    ) {
    }

    /**
     * @param Layer[] $allLayers
     */
    public function run(array $allLayers, ServerRequestInterface $psr7Request): ResponseInterface
    {
        $ctx = new Context($psr7Request, $allLayers, null, $this->resolver);
        $ctx->next();
        return $ctx->getResponse();
    }
}
