<?php

declare(strict_types=1);

namespace PTS\Next2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Next2\Layer\Resolver\LayerRequestResolver;
use PTS\Next2\Layer\Resolver\LayerResolverInterface;
use PTS\Next2\Layer\Store\LayersStore;
use PTS\Next2\Runner\Psr7Runner;

class MicroApp implements RequestHandlerInterface
{
    public LayersStore $store;
    public Psr7Runner $runner;

    public function __construct(
        LayerResolverInterface $resolver = null,
        LayersStore $store = null
    ) {
        $resolver ??= new LayerRequestResolver;
        $this->store = $store ?? new LayersStore($resolver);
        $this->runner = new Psr7Runner($resolver);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->runner->run($this->store->getLayers(), $request);
    }
}