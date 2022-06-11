<?php

namespace PTS\Next2\Layer\Normalizer;

use PTS\Next2\Layer\Layer;
use PTS\Next2\Layer\Resolver\LayerResolverInterface;

class Normalizer implements NormalizerInterface
{
    protected int $increment = 0;

    public function __construct(
        protected LayerResolverInterface $resolver
    ) {
    }

    public function normalizeLayer(Layer $layer): Layer
    {
        $layer->name = $layer->name ?: 'l-' . $this->increment++;
        $layer->regexp = $this->resolver->makeRegExp($layer);
        return $layer;
    }
}