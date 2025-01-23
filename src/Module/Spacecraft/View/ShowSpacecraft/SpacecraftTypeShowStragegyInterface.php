<?php

namespace Stu\Module\Spacecraft\View\ShowSpacecraft;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;

interface SpacecraftTypeShowStragegyInterface
{
    public function appendNavigationPart(GameControllerInterface $game): SpacecraftTypeShowStragegyInterface;

    public function setTemplateVariables(int $spacecraftId, GameControllerInterface $game): SpacecraftTypeShowStragegyInterface;

    public function getViewContext(): ViewContext;
}
