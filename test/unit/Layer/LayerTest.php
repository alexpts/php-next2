<?php

namespace PTS\Next2\Test\unit\Layer;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Layer\Layer;

class LayerTest extends TestCase
{
    public function testSetMatches(): void
    {
        $l1 = new Layer(static function() {}, '/users/');
        $l2 = $l1->setUriParams(['a' => 1]);
        $l3 = $l2->setUriParams(['a' => 2]);
        $l4 = $l3->setUriParams([]);

        static::assertSame([], $l1->uriParams);
        static::assertSame(['a' => 1], $l2->uriParams);
        static::assertSame(['a' => 2], $l3->uriParams);
        static::assertSame([], $l4->uriParams);
    }

    public function testCloneOnSetMatches(): void
    {
        $l1 = new Layer(static function() {}, '/users/');
        $l2 = $l1->setUriParams(['a' => 1]);
        $l3 = clone $l2;

        static::assertSame(['a' => 1], $l2->uriParams);
        static::assertSame([], $l3->uriParams);
    }
}