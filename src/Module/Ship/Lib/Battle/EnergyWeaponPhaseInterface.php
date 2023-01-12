<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

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
        ?ShipWrapperInterface $wrapper,
        $attackingPhalanx,
        array $targetPool,
        bool $isAlertRed = false
    ): array;

    public function fireAtBuilding(
        ShipWrapperInterface $attackerWrapper,
        PlanetFieldInterface $target,
        $isOrbitField
    ): array;

    public function getEnergyWeaponEnergyCosts(): int;
}
