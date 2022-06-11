<?php

namespace PTS\Next2\Test\functional;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Context\ContextInterface;
use PTS\Next2\HttpMethodEnum;
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

    public function testFilterByHttpMethods(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));

        $this->app->store
            ->use(function(ContextInterface $ctx, callable $next) {
                $ctx->a = 0;
                $next();
            })
            // middleware apply only for http method `POST` (skip in test)
            ->use(function(ContextInterface $ctx, callable $next) {
                $ctx->a++;
                $next();
            }, ['methods' => [HttpMethodEnum::POST->name] ])
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
            ->use(function(ContextInterface $ctx, callable $next) {
                $ctx->a = 2;
                $next();
            }, ['priority' => 100]);

        $this->app->handle($psr7Request);
        $ctx = $psr7Request->getAttribute('ctx');
        static::assertSame(1, $ctx->a);
    }
}