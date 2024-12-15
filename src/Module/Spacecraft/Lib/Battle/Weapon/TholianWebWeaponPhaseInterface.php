<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(
        ShipInterface $ship,
        ShipWrapperInterface $wrapper,
        InformationInterface $informations
    ): void;
}
