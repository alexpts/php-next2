<?php

namespace PTS\Next2\Test\unit\Layer;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Layer\LayerFactory;

class LayerFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new LayerFactory;
        $layer = $factory->create([
            static function($ctx, callable $next): void {}
        ]);

        static::assertSame(50, $layer->priority);
        static::assertNull($layer->path);
        static::assertSame([], $layer->methods);
    }

    public function testCreateFromConfig(): void
    {
        $factory = new LayerFactory;

        $config = [
            'name' => 'usersList',
            'methods' => 'GET|HEAD',
            'path' => '/users/{id}/',
            'restrictions' => [
                'id' => '\d+',
            ],
            'priority' => 250,
            'handlers' => [ function() { } ],
        ];

        $layer = $factory->createFromConfig($config);

        static::assertSame(250, $layer->priority);
        static::assertSame('usersList', $layer->name);
        static::assertSame(['GET', 'HEAD'], $layer->methods);
        static::assertSame('/users/{id}/', $layer->path);
    }
}