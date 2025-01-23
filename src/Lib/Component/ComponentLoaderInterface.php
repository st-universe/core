<?php

namespace Stu\Lib\Component;

use Stu\Module\Control\GameControllerInterface;

interface ComponentLoaderInterface
{
    /**
     * Adds the execute javascript after render.
     */
    public function loadComponentUpdates(GameControllerInterface $game): void;

    public function loadRegisteredComponents(GameControllerInterface $game): void;

    public function registerStubbedComponent(ComponentEnumInterface $componentEnum): ComponentLoaderInterface;

    public function resetStubbedComponents(): void;
}
