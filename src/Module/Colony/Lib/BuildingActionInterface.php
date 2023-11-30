<?php

namespace Stu\Module\Colony\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface BuildingActionInterface
{
    public function activate(PlanetFieldInterface $field, GameControllerInterface $game): void;

    public function deactivate(PlanetFieldInterface $field, GameControllerInterface $game): void;

    public function remove(
        PlanetFieldInterface $field,
        GameControllerInterface $game
    ): void;
}
