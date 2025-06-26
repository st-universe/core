<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\PlanetField;

interface EnergyWeaponPhaseInterface
{
    public function fire(
        EnergyAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        SpacecraftAttackCauseEnum $attackCause,
        MessageCollectionInterface $messages
    ): void;

    public function fireAtBuilding(
        EnergyAttackerInterface $attacker,
        PlanetField $target,
        bool $isOrbitField
    ): InformationWrapper;
}
