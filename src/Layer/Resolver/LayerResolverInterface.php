<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Resolver;

use Psr\Http\Message\RequestInterface;
use PTS\Next2\Layer\Layer;

interface LayerResolverInterface
{
    public function makeRegExp(Layer $layer): ?string;

    public function forRequest(
        Layer $layer,
        RequestInterface $request,
        bool $checkMethod = true,
        array &$uriParams = []
    ): ?Layer;
}
