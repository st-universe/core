<?php

namespace Stu\Module\Game\Lib\Component;

use Stu\Module\Control\GameControllerInterface;

interface ComponentLoaderInterface
{
    /**
     * If the component needs update.
     */
    public function addComponentUpdate(ComponentEnum $component, bool $isInstantUpdate = true): void;

    /**
     * Adds the execute javascript after render.
     */
    public function loadComponentUpdates(GameControllerInterface $game): void;

    /**
     * Add component, that is needed for template rendering.
     */
    public function registerComponent(ComponentEnum $component): void;

    public function loadRegisteredComponents(GameControllerInterface $game): void;

    public function resetComponents(): void;
}
