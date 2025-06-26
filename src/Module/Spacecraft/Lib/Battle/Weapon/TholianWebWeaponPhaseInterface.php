<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

interface TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(
        Ship $ship,
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $informations
    ): void;
}
