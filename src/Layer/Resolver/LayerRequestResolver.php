<?php

declare(strict_types=1);

namespace PTS\Next2\Layer\Resolver;

use Psr\Http\Message\RequestInterface;
use PTS\Next2\Layer\Layer;

use function count;
use function in_array;

class LayerRequestResolver implements LayerResolverInterface
{

    protected array $cache = [];

    public function makeRegExp(Layer $layer): ?string
    {
        $regexp = $layer->path;
        $placeholders = [];

        if ($regexp === null) {
            return null;
        }

        if (preg_match_all('~{(.*)}~Uu', $regexp, $placeholders)) {
            $allowChars = '[^\/]+'; // any exclude \/
            foreach ($placeholders[0] as $i => $match) {
                $name = $placeholders[1][$i];
                $replace = array_key_exists($name, $layer->restrictions) ? $layer->restrictions[$name] : $allowChars;
                $replace = '(?<' . $name . '>' . $replace . ')';
                $regexp = str_replace($match, $replace, $regexp);
            }
        }

        return $regexp;
    }

    public function forRequest(Layer $layer, RequestInterface $request, bool $checkMethod = true): ?Layer
    {
        if ($checkMethod && !$this->filterIsAllowMethod($layer, $request)) {
            return null;
        }

        if ($layer->path === null) {
            return $layer;
        }

        return $this->matchRegexpLayer($layer, $request);
    }

    protected function filterIsAllowMethod(Layer $layer, RequestInterface $request): bool
    {
        return !count($layer->methods) || in_array($request->getMethod(), $layer->methods, true);
    }

    protected function matchRegexpLayer(Layer $layer, RequestInterface $request): ?Layer
    {
        $uri = $request->getUri()->getPath();
        $regexp = $layer->regexp;

        // @todo reuse by cache key $uri::$regexp
        if (preg_match('~^' . $regexp . '$~Uiu', $uri, $values)) {
            $filterValues = array_filter(array_keys($values), '\is_string');
            $matches = array_intersect_key($values, array_flip($filterValues));

            return count($matches) ? $layer->setUriParams($matches) : $layer;
        }

        return null;
    }
}
