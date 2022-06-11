<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Store;

use PTS\Next2\HttpMethodEnum;

trait FastMethodsTrait
{
    public function get(string $path, callable|array $handler, array $options = []): static
    {
        return $this->method(HttpMethodEnum::GET->name, $path, $handler, $options);
    }

    public function delete(string $path, callable|array $handler, array $options = []): static
    {
        return $this->method(HttpMethodEnum::DELETE->name, $path, $handler, $options);
    }

    public function post(string $path, callable|array $handler, array $options = []): static
    {
        return $this->method(HttpMethodEnum::POST->name, $path, $handler, $options);
    }

    public function put(string $path, callable|array $handler, array $options = []): static
    {
        return $this->method(HttpMethodEnum::PUT->name, $path, $handler, $options);
    }

    public function patch(string $path, callable|array $handler, array $options = []): static
    {
        return $this->method(HttpMethodEnum::PATCH->name, $path, $handler, $options);
    }

    public function method(string $method, string $path, callable|array $handler, array $options = []): static
    {
        $options['path'] = $path;
        $options['method'] = (array)$method;
        $layer = $this->getLayerFactory()->create($handler, $options);
        return $this->addLayer($layer);
    }
}
