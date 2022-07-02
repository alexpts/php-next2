<?php

namespace PTS\Next2\Extra;

use PTS\Next2\MicroApp;

class CompositionMicroApp extends MicroApp
{
    /**
     * Merge/mount external app to current app
     */
    public function mount(MicroApp $app, string $path = null): static
    {
        foreach ($app->store->getLayers() as $layer) {
            $newLayer = clone $layer;
            if ($path !== null) {
                $newLayer->path = !$newLayer->path ? $path . '/.*' : $path . $newLayer->path;
            }

            $this->store->addLayer($newLayer);
        }

        return $this;
    }
}