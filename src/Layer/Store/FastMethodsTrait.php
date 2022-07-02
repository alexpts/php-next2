<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Store;

use PTS\Next2\HttpMethodEnum;

trait FastMethodsTrait
{
    public function get(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method(HttpMethodEnum::GET->name, $path, $handlers, $options);
    }

    public function delete(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method(HttpMethodEnum::DELETE->name, $path, $handlers, $options);
    }

    public function post(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method(HttpMethodEnum::POST->name, $path, $handlers, $options);
    }

    public function put(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method(HttpMethodEnum::PUT->name, $path, $handlers, $options);
    }

    public function patch(string $path, array|callable $handlers, array $options = []): static
    {
        return $this->method(HttpMethodEnum::PATCH->name, $path, $handlers, $options);
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
