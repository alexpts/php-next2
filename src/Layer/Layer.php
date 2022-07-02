<?php

declare(strict_types=1);

namespace PTS\Next2\Layer;

class Layer
{
    /** @var callable[] */
    public array $handlers;
    public string $name = '';
    public ?string $path = null;

    /** @var string[] */
    public array $methods = [];
    public array $restrictions = [];

    public ?string $regexp = '';
    public int $priority = 50;
    /** @var array - any data for custom cases */
    public array $context = [];

    /**
     * @param callable[] $handlers
     */
    public function __construct(
        array $handlers,
    ) {
        $this->handlers = $handlers;
    }
}
