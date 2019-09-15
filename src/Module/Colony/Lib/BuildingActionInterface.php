<?php

namespace Stu\Module\Colony\Lib;

use ColonyData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface BuildingActionInterface
{
    public function activate(ColonyData $colony, PlanetFieldInterface $field, GameControllerInterface $game): void;

    public function deactivate(ColonyData $colony, PlanetFieldInterface $field, GameControllerInterface $game): void;
}