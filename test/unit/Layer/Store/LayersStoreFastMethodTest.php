<?php

namespace PTS\Next2\Test\unit\Layer\Store;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Layer\LayerFactoryInterface;
use PTS\Next2\Layer\Resolver\LayerResolverInterface;
use PTS\Next2\Layer\Store\LayersStore;
use PTS\Next2\MicroApp;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class LayersStoreFastMethodTest extends TestCase
{
    protected LayersStore $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new LayersStore;
    }

    /**
     * @param string $httpMethod
     * @param string $expected
     * @return void
     * @dataProvider fastMethodDataProvider
     */
    public function testGetMethod(string $httpMethod, string $expected): void
    {
        $this->store->{$httpMethod}('/', function($ctx) use ($httpMethod) {
            $ctx->response = new JsonResponse(['m' => $httpMethod]);
        });

        $app = new MicroApp($this->store->getResolver(), $this->store);
        $request = new ServerRequest($httpMethod, new Uri('/'));
        $response = $app->handle($request);

        static::assertSame($expected, (string)$response->getBody());
    }

    public function fastMethodDataProvider(): array
    {
        return [
            ['GET', '{"m":"GET"}'],
            ['POST', '{"m":"POST"}'],
            ['PATCH', '{"m":"PATCH"}'],
            ['DELETE', '{"m":"DELETE"}'],
            ['PUT', '{"m":"PUT"}'],
        ];
    }
}