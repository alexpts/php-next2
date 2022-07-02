<?php

namespace PTS\Next2\Test\functional;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Context\ContextInterface;
use PTS\Next2\MicroApp;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;
use RuntimeException;

class UriParamsTest extends TestCase
{
    protected MicroApp $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new MicroApp;
    }

    public function testGetUriParams(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/users/34/'));

        $this->app->store
            ->get('/users/{id}/', function(ContextInterface $ctx) {
                $ctx->getRequest()->withAttribute('ctx', $ctx);
                $ctx->userId = $ctx->getUriParams()['id'];
            });

        $this->app->handle($psr7Request);

        static::assertSame('34', $psr7Request->getAttribute('ctx')->userId);
    }

    /**
     * @dataProvider regExpPositiveDataProvider
     */
    public function testRegExpUriParams(
        string $uri,
        string $path,
        array $restrictionParams = [],
        array $expected = []
    ): void {
        $psr7Request = new ServerRequest('GET', new Uri($uri));

        $this->app->store->get(
            $path,
            fn(ContextInterface $ctx) => $ctx->getRequest()->withAttribute('ctx', $ctx),
            ['restrictions' => $restrictionParams]
        );

        $this->app->handle($psr7Request);
        /** @var ContextInterface $ctx */
        $ctx = $psr7Request->getAttribute('ctx');

        static::assertSame($expected, $ctx->getUriParams());
    }

    public function regExpPositiveDataProvider(): array
    {
        return [
            'param' => [
                '/users/34/',
                '/users/{uid}/',
                [],
                ['uid' => '34'],
            ],
            'only number' => [
                '/users/11/',
                '/users/{uid}/',
                ['uid' => '\d+'],
                ['uid' => '11'],
            ],
            'slug [a-z-]+' => [
                '/users/alex/',
                '/users/{name}/',
                ['name' => '[a-z-]+'],
                ['name' => 'alex'],
            ]
        ];
    }

    /**
     * @dataProvider regExpNegativeDataProvider
     */
    public function testRegExpRestriction(
        string $uri,
        string $path,
        array $restrictionParams = [],
    ): void {
        $psr7Request = new ServerRequest('GET', new Uri($uri));

        $this->app->store->get(
            $path,
            fn(ContextInterface $ctx) => $ctx->getRequest()->withAttribute('ctx', $ctx),
            ['restrictions' => $restrictionParams]
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can`t delegate to layer by index 1');
        $this->app->handle($psr7Request);
    }

    public function regExpNegativeDataProvider(): array
    {
        return [
            'only number + first number not 0' => [
                '/users/013/',
                '/users/{uid}/',
                ['uid' => '[1-9][0-9]{0,}'],
            ],
            'slug only [a-z-] chars' => [
                '/users/alex-22/',
                '/users/{name}/',
                ['name' => '[a-z-]+'],
            ]
        ];
    }

}