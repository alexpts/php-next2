<?php

namespace PTS\Next2\Extra;

use PTS\Next2\MicroApp;

class CompositionMicroApp extends MicroApp
{
    /**
     * Merge/mount external app to current app
     */
    public function mount(MicroApp $app, string $prefix = ''): static
    {
        foreach ($app->store->getLayers() as $layer) {
            $newLayer = clone $layer;
            $newLayer->path = !$layer->path ? $prefix . '/.*' : $prefix . $layer->path;
            $this->store->addLayer($newLayer);
        }

        return $this;
    }
}