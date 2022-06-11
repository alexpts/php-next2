<?php

namespace PTS\Next2\Test\unit;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Context\ContextInterface;
use PTS\Next2\MicroApp;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class MicroAppTest extends TestCase
{
    protected MicroApp $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new MicroApp;
    }

    public function testMinimalApp(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $app = new MicroApp;
        $app->store->use(
            fn(ContextInterface $ctx) => $ctx->response = new JsonResponse(['message' => 'ok'])
        );
        $psr7Resp = $app->handle($psr7Request); // psr-15 runner

        static::assertSame('{"message":"ok"}', (string)$psr7Resp->getBody());
    }

    public function testArrayHandlers(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $groupHandlers = [
            function(ContextInterface $ctx, callable $next) {
                $next();
                $ctx->a1 = 1;
            },
            function(ContextInterface $ctx, callable $next) {
                $ctx->a2 = 1;
                $next();
                $ctx->response = new JsonResponse(['message' => 'ok']);
            },
        ];

        $this->app->store
            ->get('/', $groupHandlers)
            ->use(fn(ContextInterface $ctx) => $ctx->b = 1);

        $psr7Resp = $this->app->handle($psr7Request); // psr-15 runner

        static::assertSame('{"message":"ok"}', (string)$psr7Resp->getBody());
    }

    public function testGetActiveLayer(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store
            ->get('/', function(ContextInterface $ctx, callable $next) {
                static::assertSame('l-0', $ctx->getCurrentLayer()->name);
                $next();
            })
            ->get('/', function(ContextInterface $ctx) {
                static::assertSame('l-1', $ctx->getCurrentLayer()->name);
                $ctx->response = new JsonResponse(['message' => 'ok']);
            });

        $psr7Resp = $this->app->handle($psr7Request); // psr-15 runner
        static::assertSame('{"message":"ok"}', (string)$psr7Resp->getBody());
    }
}