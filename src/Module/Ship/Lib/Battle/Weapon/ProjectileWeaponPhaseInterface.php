<?php

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface ProjectileWeaponPhaseInterface
{
    /**
     * @param ShipWrapperInterface[] $targetPool
     * 
     * @return FightMessageInterface[]
     */
    public function fire(
        ProjectileAttackerInterface $attacker,
        array $targetPool,
        bool $isAlertRed = false
    ): array;

    /**
     * @return array<string>
     */
    public function fireAtBuilding(
        ProjectileAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField,
        int &$antiParticleCount
    ): array;
}
