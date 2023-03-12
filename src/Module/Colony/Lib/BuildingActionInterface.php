<?php

namespace Stu\Module\Colony\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface BuildingActionInterface
{
    public function activate(ColonyInterface $colony, PlanetFieldInterface $field, GameControllerInterface $game): void;

    public function deactivate(ColonyInterface $colony, PlanetFieldInterface $field, GameControllerInterface $game): void;

    public function remove(
        ColonyInterface $colony,
        PlanetFieldInterface $field,
        GameControllerInterface $game,
        bool $upgrade = false
    ): void;
}
