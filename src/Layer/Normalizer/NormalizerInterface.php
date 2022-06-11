<?php

namespace PTS\Next2\Layer\Normalizer;

use PTS\Next2\Layer\Layer;

interface NormalizerInterface
{
    public function normalizeLayer(Layer $layer): Layer;
}