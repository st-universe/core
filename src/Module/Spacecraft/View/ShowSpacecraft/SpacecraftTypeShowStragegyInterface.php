<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraft;

use Stu\Module\Control\Component\View\ViewControllerContext;

interface SpacecraftTypeShowStragegyInterface
{
    public function appendNavigationPart(ViewControllerContext $game): SpacecraftTypeShowStragegyInterface;

    public function setTemplateVariables(int $spacecraftId, ViewControllerContext $game): SpacecraftTypeShowStragegyInterface;
}
