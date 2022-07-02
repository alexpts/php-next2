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
        $layer = new Layer([ fn() => 0 ]);
        $layer->path = $path;
        $layer->restrictions = $restrictions;

        $resolver = new LayerRequestResolver;
        $regexp = $resolver->makeRegExp($layer);
        static::assertSame($expected, $regexp);
    }

    public function testInlineRestriction(): void
    {
        $layer = new Layer([ fn() => 0 ]);
        $layer->path = '/users/{id:\d+}/city/{slug}/';

        $resolver = new LayerRequestResolver;
        $regexp = $resolver->makeRegExp($layer);

        $expected = '~^/users/(?<id>\d+)/city/(?<slug>[^\/]+)/$~Uiu';
        static::assertSame($expected, $regexp);
    }

    public function regExpDataProvider(): array
    {
        return [
            'no params' => [
                '/users/',
                '~^/users/$~Uiu',
                []
            ],
            'single param' => [
                '/users/{id}/',
                '~^/users/(?<id>[^\/]+)/$~Uiu',
                [],
            ],
            'single number only param' => [
                '/users/{id}/',
                '~^/users/(?<id>\d+)/$~Uiu',
                ['id' => '\d+'],
            ],
            '2 params' => [
                '/users/{id}/city/{slug}/',
                '~^/users/(?<id>[^\/]+)/city/(?<slug>[^\/]+)/$~Uiu',
                [],
            ],
            '2 params + restrictions' => [
                '/users/{id}/city/{slug}/',
                '~^/users/(?<id>\d+)/city/(?<slug>[a-z-]+)/$~Uiu',
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

        $match = [];
        $layer = $resolver->forRequest($layer, $request, $checkHttpMethod, $match);

        static::assertNotNull($layer);
        static::assertSame($expectMatches, $match);
    }

    public function positiveMatchDataProvider(): array
    {
        return [
            'without path (middleware for any request)' => [
                [
                    'handlers' => [ fn() => 0 ]
                ],
                new ServerRequest('GET', new Uri('/users/')),
                []
            ],
            'without method' => [
                [
                    'path' => '/users/',
                    'handlers' => [ fn() => 0 ]
                ],
                new ServerRequest('GET', new Uri('/users/')),
                []
            ],
            'with method' => [
                [
                    'path' => '/users/',
                    'method' => 'GET',
                    'handlers' => [ fn() => 0 ]
                ],
                new ServerRequest('GET', new Uri('/users/')),
                []
            ],
            'skip check method' => [
                [
                    'path' => '/users/',
                    'methods' => 'GET',
                    'handlers' => [ fn() => 0]
                ],
                new ServerRequest('POST', new Uri('/users/')),
                [],
                false
            ],
            'match param' => [
                [
                    'path' => '/users/{id}/',
                    'methods' => 'GET',
                    'handlers' => [ fn() => 0 ]
                ],
                new ServerRequest('GET', new Uri('/users/34/')),
                ['id' => '34']
            ],
            'match param + restrictions' => [
                [
                    'path' => '/users/{id}/',
                    'methods' => 'GET',
                    'handlers' => [ fn() => 0 ],
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

        [$layer] = $resolver->forRequest($layer, $request, $checkHttpMethod);
        static::assertNull($layer);
    }

    public function negativeMatchDataProvider(): array
    {
        return [
            'any path' => [
                [
                    'path' => '/users/',
                    'handlers' => [ fn() => 0 ]
                ],
                new ServerRequest('GET', new Uri('/')),
            ],
            'match param + not pass restrictions ' => [
                [
                    'path' => '/users/{id}/',
                    'methods' => 'GET',
                    'handlers' => [ fn() => 0 ],
                    'restrictions' => ['id' => '\d+']
                ],
                new ServerRequest('GET', new Uri('/users/alex/')),
            ],
            'any method' => [
                [
                    'path' => '/users/',
                    'methods' => 'GET|HEAD',
                    'handlers' => [ fn() => 0 ]
                ],
                new ServerRequest('POST', new Uri('/users/')),
            ],
        ];
    }
}