<?php

namespace PTS\Next2\Test\unit\Layer\Resolver;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PTS\Next2\Layer\Layer;
use PTS\Next2\Layer\LayerFactory;
use PTS\Next2\Layer\Resolver\LayerRequestResolver;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class LayerRequestResolverTest extends TestCase
{
    /**
     * @dataProvider regExpDataProvider
     */
    public function testRegExp(string $path, string $expected, array $restrictions = []): void
    {
        $layer = new Layer(fn() => 0, $path);
        $layer->restrictions = $restrictions;

        $resolver = new LayerRequestResolver;
        $regexp = $resolver->makeRegExp($layer);
        static::assertSame($expected, $regexp);
    }

    public function regExpDataProvider(): array
    {
        return [
            'no params' => [
                '/users/',
                '/users/',
                []
            ],
            'single param' => [
                '/users/{id}/',
                '/users/(?<id>[^\/]+)/',
                [],
            ],
            'single number only param' => [
                '/users/{id}/',
                '/users/(?<id>\d+)/',
                ['id' => '\d+'],
            ],
            '2 params' => [
                '/users/{id}/city/{slug}/',
                '/users/(?<id>[^\/]+)/city/(?<slug>[^\/]+)/',
                [],
            ],
            '2 params + restrictions' => [
                '/users/{id}/city/{slug}/',
                '/users/(?<id>\d+)/city/(?<slug>[a-z-]+)/',
                ['id' => '\d+', 'slug' => '[a-z-]+'],
            ]
        ];
    }

    /**
     * @dataProvider positiveMatchDataProvider
     */
    public function testMatchRegexpLayer(
        array $layerConfig,
        ServerRequestInterface $request,
        array $expectMatches = [],
        bool $checkHttpMethod = true,
    ): void {
        $resolver = new LayerRequestResolver;
        $factory = new LayerFactory;

        $layer = $factory->createFromConfig($layerConfig);
        $layer->regexp = $resolver->makeRegExp($layer);

        $layer = $resolver->forRequest($layer, $request, $checkHttpMethod);

        static::assertNotNull($layer);
        static::assertSame($expectMatches, $layer->uriParams);
    }

    public function positiveMatchDataProvider(): array
    {
        return [
            'without path (middleware for any request)' => [
                ['handler' => fn() => 0],
                new ServerRequest('GET', new Uri('/users/')),
                []
            ],
            'without method' => [
                ['path' => '/users/', 'handler' => fn() => 0],
                new ServerRequest('GET', new Uri('/users/')),
                []
            ],
            'with method' => [
                ['path' => '/users/', 'method' => 'GET', 'handler' => fn() => 0],
                new ServerRequest('GET', new Uri('/users/')),
                []
            ],
            'skip check method' => [
                ['path' => '/users/', 'methods' => ['GET'], 'handler' => fn() => 0],
                new ServerRequest('POST', new Uri('/users/')),
                [],
                false
            ],
            'match param' => [
                ['path' => '/users/{id}/', 'methods' => ['GET'], 'handler' => fn() => 0],
                new ServerRequest('GET', new Uri('/users/34/')),
                ['id' => '34']
            ],
            'match param + restrictions' => [
                [
                    'path' => '/users/{id}/',
                    'methods' => ['GET'],
                    'handler' => fn() => 0,
                    'restrictions' => ['id' => '\d+']
                ],
                new ServerRequest('GET', new Uri('/users/34/')),
                ['id' => '34'] // http type string always, \d+ - number only string type
            ],
        ];
    }

    /**
     * @dataProvider negativeMatchDataProvider
     */
    public function testNotMatchRegexpLayer(
        array $layerConfig,
        ServerRequestInterface $request,
        array $expectMatches = [],
        bool $checkHttpMethod = true,
    ): void {
        $resolver = new LayerRequestResolver;
        $factory = new LayerFactory;

        $layer = $factory->createFromConfig($layerConfig);
        $layer->regexp = $resolver->makeRegExp($layer);

        $layer = $resolver->forRequest($layer, $request, $checkHttpMethod);
        static::assertNull($layer);
    }

    public function negativeMatchDataProvider(): array
    {
        return [
            'any path' => [
                ['path' => '/users/', 'handler' => fn() => 0],
                new ServerRequest('GET', new Uri('/')),
            ],
            'match param + not pass restrictions ' => [
                [
                    'path' => '/users/{id}/',
                    'methods' => ['GET'],
                    'handler' => fn() => 0,
                    'restrictions' => ['id' => '\d+']
                ],
                new ServerRequest('GET', new Uri('/users/alex/')),
            ],
            'any method' => [
                ['path' => '/users/', 'methods' => ['GET'], 'handler' => fn() => 0],
                new ServerRequest('POST', new Uri('/users/')),
            ],
        ];
    }
}