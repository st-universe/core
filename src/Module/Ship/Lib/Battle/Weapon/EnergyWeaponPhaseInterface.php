<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface EnergyWeaponPhaseInterface
{
    /**
     * @param ShipWrapperInterface[] $targetPool
     * 
     * @return FightMessageInterface[]
     */
    public function fire(
        EnergyAttackerInterface $attacker,
        array $targetPool,
        bool $isAlertRed = false
    ): array;

    /**
     * @return array<string>
     */
    public function fireAtBuilding(
        EnergyAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): array;
}
