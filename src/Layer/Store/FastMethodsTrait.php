<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Store;

trait FastMethodsTrait
{
    public function get(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method('GET', $path, $handlers, $options);
    }

    public function delete(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method('DELETE', $path, $handlers, $options);
    }

    public function post(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method('POST', $path, $handlers, $options);
    }

    public function put(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method('PUT', $path, $handlers, $options);
    }

    public function patch(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method('PATCH', $path, $handlers, $options);
    }

    public function method(string $method, string $path, array|callable $handlers, array $options = []): static
    {
        $handlers = is_array($handlers) ? $handlers : [$handlers];

        $options['path'] = $path;
        $options['method'] = (array)$method;
        $layer = $this->getLayerFactory()->create($handlers, $options);
        return $this->addLayer($layer);
    }
}
