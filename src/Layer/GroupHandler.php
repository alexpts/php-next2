<?php

declare(strict_types=1);

namespace PTS\Next2\Layer;

use PTS\Next2\Context\Context;

class GroupHandler
{
    /** @var callable[] */
    protected array $handlers;

    /**
     * @param callable[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = array_filter($handlers, 'is_callable');
    }

    public function __invoke(Context $ctx, $next): void
    {
        $this->run($ctx, 0, $next);
    }

    public function run(Context $ctx, int $i, callable $next): void
    {
        $handler = $this->handlers[$i] ?? null;
        if ($handler === null) {
            $next();
            return;
        }

        $handler($ctx, fn() => $this->run($ctx, $i + 1, $next));
    }
}
