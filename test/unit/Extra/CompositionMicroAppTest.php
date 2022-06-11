<?php

namespace PTS\Next2\Test\unit\Extra;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Extra\CompositionMicroApp;
use PTS\Next2\MicroApp;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class CompositionMicroAppTest extends TestCase
{
    protected CompositionMicroApp $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new CompositionMicroApp;
    }

    public function test(): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/api/v1/users/'));

        $apiAppV1 = new MicroApp;
        $apiAppV1->store->get('/users/', fn($ctx) => $ctx->response = new JsonResponse(['v1' => 'users']));

        $apiAppV2 = new MicroApp;
        $apiAppV2->store->get('/users/', fn($ctx) => $ctx->response = new JsonResponse(['v2' => 'users']));

        $reuseApp = new MicroApp;
        $reuseApp->store->get('/users/', fn($ctx) => $ctx->response = new JsonResponse(['reuse' => 'users']));

        $this->app
            ->mount($apiAppV1, '/api/v1')
            ->mount($apiAppV2, '/api/v2')
            ->mount($reuseApp); // merge layers without prefix to app

        $psr7Resp = $this->app->handle($psr7Request);

        static::assertSame('{"v1":"users"}', (string)$psr7Resp->getBody());
    }
}