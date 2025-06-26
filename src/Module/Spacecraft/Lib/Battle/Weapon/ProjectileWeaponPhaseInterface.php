<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\PlanetField;

interface ProjectileWeaponPhaseInterface
{
    public function fire(
        ProjectileAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        SpacecraftAttackCauseEnum $attackCause,
        MessageCollectionInterface $messages
    ): void;

    public function fireAtBuilding(
        ProjectileAttackerInterface $attacker,
        PlanetField $target,
        bool $isOrbitField,
        int &$antiParticleCount
    ): InformationWrapper;
}
