<?php

namespace PTS\Next2\Test\unit\Layer;

use PHPUnit\Framework\TestCase;
use PTS\Next2\Context\Context;
use PTS\Next2\Layer\GroupHandler;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class GroupHandlerTest extends TestCase
{
    public function testRun(): void
    {
        $handlers = [
            function($ctx, callable $next) {
                $ctx->first = 1;
                $next();
                $ctx->first++;
            },
            function($ctx, callable $next) {
                $ctx->second = 1;
                $next();
                $ctx->second++;
            }
        ];

        $psr7Request = new ServerRequest('GET', new Uri('/'));
        $ctx = new Context($psr7Request);

        $gHandler = new GroupHandler($handlers);
        $gHandler($ctx, fn() => $ctx->last = 1);

        static::assertSame(2, $ctx->first);
        static::assertSame(2, $ctx->second);
        static::assertSame(1, $ctx->last);
    }

    /**
     * @param callable[] $handlers
     * @param string $error
     * @param callable $asserts
     * @return void
     *
     * @dataProvider orderHandlerDataProvider
     */
    public function testOrderHandler(array $handlers, string $error, callable $asserts): void
    {
        $psr7Request = new ServerRequest('GET', new Uri('/'));
        $ctx = new Context($psr7Request);

        try {
            $gHandler = new GroupHandler($handlers);
            $gHandler($ctx, fn() => $ctx->last = 1);
        } catch (\Throwable $throwable) {
            static::assertSame($error, $throwable->getMessage());
            $asserts($ctx);
        }
    }

    public function orderHandlerDataProvider(): array
    {
        return [
            'in 1 request' => [
                [
                    function($ctx, callable $next) {
                        $ctx->first = 1;
                        throw new \RuntimeException('request first');
                        $next();
                        $ctx->first++;
                    },
                    function($ctx, callable $next) {
                        $ctx->second = 1;
                        $next();
                        $ctx->second++;
                    }
                ],
                'request first',
                function($ctx) {
                    static::assertSame(1, $ctx->first);
                    static::assertSame(null, $ctx->second ?? null);
                    static::assertSame(null, $ctx->last ?? null);
                }
            ],

            'in 2 request' => [
                [
                    function($ctx, callable $next) {
                        $ctx->first = 1;
                        $next();
                        $ctx->first++;
                    },
                    function($ctx, callable $next) {
                        $ctx->second = 1;
                        throw new \RuntimeException('request second');
                        $next();
                        $ctx->second++;
                    }
                ],
                'request second',
                function($ctx) {
                    static::assertSame(1, $ctx->first);
                    static::assertSame(1, $ctx->second);
                    static::assertSame(null, $ctx->last ?? null);
                }
            ],

            'before 1 response' => [
                [
                    function($ctx, callable $next) {
                        $ctx->first = 1;
                        $next();
                        throw new \RuntimeException('before 1 response');
                        $ctx->first++;
                    },
                    function($ctx, callable $next) {
                        $ctx->second = 1;
                        $next();
                        $ctx->second++;
                    }
                ],
                'before 1 response',
                function($ctx) {
                    static::assertSame(1, $ctx->first);
                    static::assertSame(2, $ctx->second);
                    static::assertSame(1, $ctx->last);
                }
            ],

            'before 2 response' => [
                [
                    function($ctx, callable $next) {
                        $ctx->first = 1;
                        $next();
                        $ctx->first++;
                    },
                    function($ctx, callable $next) {
                        $ctx->second = 1;
                        $next();
                        throw new \RuntimeException('before 2 response');
                        $ctx->second++;
                    }
                ],
                'before 2 response',
                function($ctx) {
                    static::assertSame(1, $ctx->first);
                    static::assertSame(1, $ctx->second);
                    static::assertSame(1, $ctx->last);
                }
            ],

        ];
    }
}