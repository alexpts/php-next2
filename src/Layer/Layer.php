<?php

declare(strict_types=1);

namespace PTS\Next2\Layer;

class Layer
{
    /** @var callable */
    public $handler;
    public string $name = '';

    /** @var string[] */
    public array $methods = [];
    public array $restrictions = [];

    public ?string $regexp = '';
    public int $priority = 50;
    /** @var array - any data for custom cases */
    public array $context = [];

    /** @var array<string, string> */
    public array $uriParams = []; // need clone Layer on each diff

    public function __construct(
        callable $handler,
        public ?string $path = null,
    ) {
        $this->handler = $handler;
    }

    public function __clone()
    {
        $this->uriParams = [];
    }

    /**
     * @Immutable
     *
     * @param array $uriParams
     * @return $this
     */
    public function setUriParams(array $uriParams): static
    {
        $clone = clone $this;
        $clone->uriParams = $uriParams;
        return $clone;
    }
}
