<?php

namespace Stu\Module\Colony\Lib;

use ColfieldData;
use ColonyData;
use Stu\Control\GameControllerInterface;

interface BuildingActionInterface
{
    public function activate(ColonyData $colony, ColfieldData $field, GameControllerInterface $game): void;

    public function deactivate(ColonyData $colony, ColfieldData $field, GameControllerInterface $game): void;
}