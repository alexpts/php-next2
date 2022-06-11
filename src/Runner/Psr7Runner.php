<?php

declare(strict_types=1);

namespace PTS\Next2\Runner;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Context\Context;
use PTS\Next2\Layer\Layer;
use PTS\Next2\Layer\Resolver\LayerRequestResolver;
use PTS\Next2\Layer\Resolver\LayerResolverInterface;

class Psr7Runner
{
    public function __construct(
        protected LayerResolverInterface $resolver = new LayerRequestResolver,
        protected $contextRunner = new ContextRunner,
    ) {
    }

    /**
     * @param Layer[] $allLayers
     */
    public function run(array $allLayers, ServerRequestInterface $psr7Request): ResponseInterface
    {
        // @todo think about lazy resolve, first handler can create response and not need resolve all handlers
        $layers = $this->findLayersForRequest($allLayers, $psr7Request);

        $context = new Context($psr7Request);
        $this->contextRunner->run($layers, $context, 0);
        return $context->getResponse();
    }

    /**
     * @return Layer[]
     */
    public function findLayersForRequest(array $layers, ServerRequestInterface $request): array
    {
        $requestLayers = [];
        foreach ($layers as $layer) {
            $rLayer = $this->resolver->forRequest($layer, $request);
            if ($rLayer !== null) {
                $requestLayers[] = $rLayer;
            }
        }

        return $requestLayers;
    }
}
