<?php

namespace PTS\Next2\Extra;

use PTS\Next2\MicroApp;

class CompositionMicroApp extends MicroApp
{
    /**
     * Merge/mount external app to current app
     *
     * @param MicroApp $app
     * @param string|null $path
     *
     * @return $this
     */
    public function mount(MicroApp $app, string $path = null): static
    {
        foreach ($app->store->getLayers() as $layer) {
            if (null === $path) {
                $this->store->addLayer($layer);
                continue;
            }

            $newLayer = clone $layer;
            $newLayer->path = !$layer->path ? $path . '/.*' : $path . $layer->path;
            $this->store->addLayer($newLayer);
        }

        return $this;
    }
}