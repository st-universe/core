<?php

namespace Stu\Module\Colony\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\PlanetField;

interface BuildingActionInterface
{
    public function activate(PlanetField $field, GameControllerInterface $game): void;

    public function deactivate(PlanetField $field, GameControllerInterface $game): void;

    public function remove(
        PlanetField $field,
        GameControllerInterface $game,
        bool $isDueToUpgrade = false
    ): void;
}
