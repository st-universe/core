<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Orm\Entity\PlanetFieldInterface;

interface EnergyWeaponPhaseInterface
{
    /**
     * @return MessageInterface[]
     */
    public function fire(
        EnergyAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        ShipAttackCauseEnum $attackCause
    ): array;

    public function fireAtBuilding(
        EnergyAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper;
}
