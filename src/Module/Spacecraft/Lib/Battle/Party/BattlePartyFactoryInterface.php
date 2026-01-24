<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;

interface BattlePartyFactoryInterface
{
    public function createAlertStateBattleParty(
        SpacecraftWrapperInterface $leaderWrapper
    ): AlertStateBattleParty;

    public function createIncomingBattleParty(
        SpacecraftWrapperInterface $leaderWrapper
    ): IncomingBattleParty;

    public function createRoundBasedBattleParty(
        BattlePartyInterface $battleParty
    ): RoundBasedBattleParty;

    public function createAttackingBattleParty(
        SpacecraftWrapperInterface|FleetWrapperInterface $wrapper,
        bool $isAttackingShieldsOnly
    ): AttackingBattleParty;

    public function createAttackedBattleParty(
        SpacecraftWrapperInterface $wrapper
    ): AttackedBattleParty;

    public function createSingletonBattleParty(
        SpacecraftWrapperInterface $wrapper
    ): SingletonBattleParty;

    /** @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers */
    public function createMixedBattleParty(
        Collection $wrappers
    ): MixedBattleParty;

    public function createColonyDefendingBattleParty(
        Ship $leader
    ): ColonyDefendingBattleParty;

    public function createPirateFleetBattleParty(
        Fleet $fleet
    ): PirateFleetBattleParty;
}
