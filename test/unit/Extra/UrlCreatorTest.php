<?php

namespace PTS\Next2\Test\unit\Extra;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Extra\UrlCreator;
use PTS\Next2\MicroApp;
use PTS\Psr7\Response\JsonResponse;

class UrlCreatorTest extends TestCase
{
    public function testGood(): void
    {
        $app = new MicroApp;
        $urlCreator = new UrlCreator($app);

        $app->store
            ->get('/users/{id}/', fn() => new JsonResponse(['status' => 200]), ['name' => 'userRoute']);

        $query = ['format' => 'json', 'rel' => 'site'];
        $path = $urlCreator->url('userRoute', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        static::assertSame($expected, $path);
    }

    public function testNotFound(): void
    {
        $app = new MicroApp;
        $urlCreator = new UrlCreator($app);

        $app->store
            ->get('/users/{id}/', fn() => new JsonResponse(['status' => 200]));

        $path = $urlCreator->url('user', ['id' => 34]);
        static::assertNull($path);
    }
}