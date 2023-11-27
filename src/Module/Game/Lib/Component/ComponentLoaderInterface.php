<?php

namespace Stu\Module\Game\Lib\Component;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Twig\TwigPageInterface;

interface ComponentLoaderInterface
{
    /**
     * If the component needs update.
     */
    public function addComponentUpdate(ComponentEnum $component): void;

    /** 
     * Adds the execute javascript after render.
     */
    public function loadComponentUpdates(GameControllerInterface $game): void;

    /**
     * Add component, that is needed for template rendering.
     */
    public function registerComponent(ComponentEnum $component): void;

    public function loadRegisteredComponents(TwigPageInterface $twigPage, GameControllerInterface $game): void;
}
