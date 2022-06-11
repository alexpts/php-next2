<?php

namespace PTS\Next2\Test\unit\Layer\Store;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Layer\LayerFactoryInterface;
use PTS\Next2\Layer\Resolver\LayerResolverInterface;
use PTS\Next2\Layer\Store\LayersStore;

class LayersStoreTest extends TestCase
{
    protected LayersStore $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new LayersStore;
    }

    public function testGetLayerFactory(): void
    {
        $factory = $this->store->getLayerFactory();
        $this->assertNotNull($factory);
        $this->assertInstanceOf(LayerFactoryInterface::class, $factory);
    }

    public function testGetResolver(): void
    {
        $resolver = $this->store->getResolver();
        $this->assertNotNull($resolver);
        $this->assertInstanceOf(LayerResolverInterface::class, $resolver);
    }
}