<?php

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsItem;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

interface FightLibInterface
{
    public function ready(
        SpacecraftWrapperInterface $wrapper,
        bool $isUndockingMandatory,
        InformationInterface $informations
    ): void;

    public function canAttackTarget(
        Spacecraft $spacecraft,
        Spacecraft|SpacecraftNfsItem $nfsItem,
        bool $checkCloaked = false,
        bool $checkActiveWeapons = true,
        bool $checkWarped = true
    ): bool;

    /**
     * @return array{0: AttackingBattleParty, 1: AttackedBattleParty, 2: bool}
     */
    public function getAttackersAndDefenders(
        SpacecraftWrapperInterface|FleetWrapperInterface $wrapper,
        SpacecraftWrapperInterface $target,
        bool $isAttackingShieldsOnly,
        BattlePartyFactoryInterface $battlePartyFactory
    ): array;

    public function calculateHealthPercentage(Ship $target): int;
}
