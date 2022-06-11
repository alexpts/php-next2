<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Store;

use PTS\Next2\Layer\{
    Layer,
    LayerFactory,
    LayerFactoryInterface,
    Normalizer\Normalizer,
    Normalizer\NormalizerInterface,
    Resolver\LayerRequestResolver,
    Resolver\LayerResolverInterface
};

class LayersStore
{
    use FastMethodsTrait;

    protected LayerResolverInterface $resolver;
    protected LayerFactoryInterface $layerFactory;
    protected NormalizerInterface $normalizer;

    /** @var Layer[] */
    protected array $layers = [];
    protected bool $sorted = false;

    public function __construct(
        LayerResolverInterface $resolver = null,
        LayerFactoryInterface $factory = null,
        NormalizerInterface $normalizer = null,
    ) {
        $this->resolver = $resolver ?? new LayerRequestResolver;
        $this->layerFactory = $factory ?? new LayerFactory;
        $this->normalizer = $normalizer ?? new Normalizer($this->resolver);
    }

    public function getResolver(): LayerResolverInterface
    {
        return $this->resolver;
    }

    public function getLayerFactory(): LayerFactoryInterface
    {
        return $this->layerFactory;
    }

    public function addLayer(Layer $layer): static
    {
        $this->layers[] = $this->normalizer->normalizeLayer($layer);
        $this->sorted = false;

        return $this;
    }

    /**
     * @param callable[] | callable $handler
     */
    public function use(callable|array $handler, array $options = []): static
    {
        $layer = $this->layerFactory->create($handler, $options);
        return $this->addLayer($layer);
    }

    /**
     * @return Layer[]
     */
    public function getLayers(): array
    {
        if ($this->sorted === false) {
            $this->sortByPriority();
        }

        return $this->layers;
    }

    protected function sortByPriority(): static
    {
        if ($this->layers) {
            $sorted = [];

            foreach ($this->layers as $layer) {
                $sorted[$layer->priority][] = $layer;
            }

            krsort($sorted, SORT_NUMERIC);
            $this->layers = array_merge(...$sorted);
        }

        $this->sorted = true;
        return $this;
    }
}
