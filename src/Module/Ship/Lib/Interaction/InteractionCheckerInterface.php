<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface InteractionCheckerInterface
{
    public function checkPosition(ShipInterface $shipa, ShipInterface $shipb): bool;

    public function checkColonyPosition(ColonyInterface $col, ShipInterface $ship): bool;

    public static function canInteractWith(
        ShipInterface $ship,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game,
        bool $doCloakCheck = false
    ): bool;
}
