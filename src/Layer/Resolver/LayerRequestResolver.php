<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Resolver;

use Psr\Http\Message\RequestInterface;
use PTS\Next2\Layer\Layer;

use function count;
use function in_array;

class LayerRequestResolver implements LayerResolverInterface
{
    public function makeRegExp(Layer $layer): ?string
    {
        $regexp = $layer->path;
        if ($regexp === null) {
            return null;
        }

        $placeholders = [];

        if (preg_match_all('~{(?<name>.*)(:(?<restrict>.*))?}~Uu', $regexp, $placeholders)) {
            foreach ($placeholders[0] as $i => $match) {
                $name = $placeholders['name'][$i];
                $replace = $this->getRestrict($placeholders['restrict'][$i], $layer->restrictions[$name] ?? null);
                $replace = '(?<' . $name . '>' . $replace . ')';
                $regexp = str_replace($match, $replace, $regexp);
            }
        }

        return '~^' . $regexp . '$~Uiu';
    }

    /**
     * Config restrict has more priorite that inline
     */
    protected function getRestrict(string $inlineRestrict, ?string $configRestrict): string
    {
        $restrict = $configRestrict ?? $inlineRestrict;
        return $restrict ?: '[^\/]+';
    }

    public function forRequest(
        Layer $layer,
        RequestInterface $request,
        bool $checkMethod = true,
        array &$uriParams = []
    ): ?Layer {
        if ($checkMethod && !$this->filterIsAllowMethod($layer, $request)) {
            return null;
        }

        if ($layer->path === null) {
            return $layer;
        }

        return $this->matchRegexpLayer($layer, $request, $uriParams);
    }

    protected function filterIsAllowMethod(Layer $layer, RequestInterface $request): bool
    {
        return !count($layer->methods) || in_array($request->getMethod(), $layer->methods, true);
    }

    protected function matchRegexpLayer(Layer $layer, RequestInterface $request, array &$matches): ?Layer
    {
        $uri = $request->getUri()->getPath();

        // @todo reuse by cache key $uri::$regexp
        if (preg_match($layer->regexp, $uri, $values)) {
            $filterValues = array_filter(array_keys($values), '\is_string');
            $matches = array_intersect_key($values, array_flip($filterValues));

            return $layer;
        }

        return null;
    }
}
