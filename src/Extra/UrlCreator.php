<?php

declare(strict_types=1);

namespace PTS\Next2\Extra;

use PTS\Next2\Layer\Layer;
use PTS\Next2\MicroApp;

class UrlCreator
{
    protected MicroApp $app;

    public function __construct(MicroApp $app)
    {
        $this->app = $app;
    }

    public function url(string $name, array $placeholders = [], array $options = []): ?string
    {
        $layers = $this->app->store->getLayers();
        $layer = $this->findLayerByName($layers, $name);

        return $layer ? $this->create($layer, $placeholders, $options) : null;
    }

    protected function findLayerByName(array $layers, string $name): ?Layer
    {
        foreach ($layers as $layer) {
            if ($layer->path && $layer->name === $name) {
                return $layer;
            }
        }

        return null;
    }

    protected function create(Layer $layer, array $placeholders, array $options): string
    {
        $placeholders = $this->prepareUrlPlaceholder($placeholders);

        $url = str_replace(array_keys($placeholders), $placeholders, $layer->path);

        if (isset($options['query'])) {
            $url .= '?' . http_build_query($options['query']);
        }

        return $url;
    }

    /**
     * @param array $placeholders
     * @return array
     */
    protected function prepareUrlPlaceholder(array $placeholders): array
    {
        $prepared = [];
        foreach ($placeholders as $name => $value) {
            $prepared['{' . $name . '}'] = $value;
        }

        return $prepared;
    }
}