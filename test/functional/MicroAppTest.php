<?php

namespace PTS\Next2\Test\functional;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Context\ContextInterface;
use PTS\Next2\MicroApp;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;
use RuntimeException;

class MicroAppTest extends TestCase
{
    protected MicroApp $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new MicroApp;
    }

    public function testFilterByHttpMethods(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store
            ->use(function(ContextInterface $ctx) {
                $ctx->a = 0;
                $ctx->next();
            })
            // middleware apply only for http method `POST` (skip in test)
            ->use(function(ContextInterface $ctx) {
                $ctx->a++;
                $ctx->next();
            }, [
                'methods' => 'POST'
            ])
            ->use(function(ContextInterface $ctx) {
                $ctx->getRequest()->withAttribute('ctx', $ctx);
                $ctx->response = new JsonResponse(['message' => 'ok']);
            });

        $this->app->handle($psr7Request);
        $ctx = $psr7Request->getAttribute('ctx');
        static::assertSame(0, $ctx->a);
    }

    public function testPriority(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store
            ->use(function(ContextInterface $ctx) {
                $ctx->a = 1;
                $ctx->getRequest()->withAttribute('ctx', $ctx);
                $ctx->response = new JsonResponse(['message' => 'ok']);
            }, ['priority' => 10])
            ->use(function(ContextInterface $ctx) {
                $ctx->a = 2;
                $ctx->next();
            }, ['priority' => 100]);

        $this->app->handle($psr7Request);
        $ctx = $psr7Request->getAttribute('ctx');
        static::assertSame(1, $ctx->a);
    }

    public function testUseHandler(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store
            ->use(fn(ContextInterface $ctx) => $ctx->getResponse()->withStatus(422));

        $psr7Response = $this->app->handle($psr7Request);
        static::assertSame(422, $psr7Response->getStatusCode());
    }

    public function testDelegateToNotDefineLayer(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store
            ->use(fn(ContextInterface $ctx) => $ctx->next());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can`t delegate to layer by index 1');
        $this->app->handle($psr7Request);
    }

    public function testSetResponse(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store->use(function(ContextInterface $ctx) {
            $response = new JsonResponse(['ok' => true]);
            $ctx->setResponse($response);
        });

        $psr7Response = $this->app->handle($psr7Request);
        static::assertSame(200, $psr7Response->getStatusCode());
        static::assertSame('{"ok":true}', (string)$psr7Response->getBody());
    }
}