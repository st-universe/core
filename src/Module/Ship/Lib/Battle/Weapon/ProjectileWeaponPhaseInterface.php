<?php

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface ProjectileWeaponPhaseInterface
{
    public function fire(
        ProjectileAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        ShipAttackCauseEnum $attackCause,
        MessageCollectionInterface $messages
    ): void;

    public function fireAtBuilding(
        ProjectileAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField,
        int &$antiParticleCount
    ): InformationWrapper;
}
