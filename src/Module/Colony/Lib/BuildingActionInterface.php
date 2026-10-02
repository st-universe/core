<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Module\Control\Component\ControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\PlanetField;

interface BuildingActionInterface
{
    public function activate(PlanetField $field, ControllerContext $game): void;

    public function deactivate(PlanetField $field, ControllerContext $game): void;

    public function remove(
        PlanetField $field,
        ControllerContext $game,
        bool $isDueToUpgrade = false
    ): void;
}
