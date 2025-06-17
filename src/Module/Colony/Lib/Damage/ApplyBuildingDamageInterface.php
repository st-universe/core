<?php

namespace Stu\Module\Colony\Lib\Damage;

use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\PlanetFieldInterface;

interface ApplyBuildingDamageInterface
{
    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper;
}
