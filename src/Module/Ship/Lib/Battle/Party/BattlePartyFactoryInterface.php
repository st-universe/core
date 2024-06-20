<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface BattlePartyFactoryInterface
{
    public function createAlertStateBattleParty(
        ShipWrapperInterface $leaderWrapper
    ): AlertStateBattleParty;

    public function createIncomingBattleParty(
        ShipWrapperInterface $leaderWrapper
    ): IncomingBattleParty;

    public function createRoundBasedBattleParty(
        BattlePartyInterface $battleParty
    ): RoundBasedBattleParty;

    public function createAttackingBattleParty(
        ShipWrapperInterface|FleetWrapperInterface $wrapper
    ): AttackingBattleParty;

    public function createAttackedBattleParty(
        ShipWrapperInterface $wrapper
    ): AttackedBattleParty;

    public function createSingletonBattleParty(
        ShipWrapperInterface $wrapper
    ): SingletonBattleParty;

    /** @param Collection<int, ShipWrapperInterface> $wrappers */
    public function createMixedBattleParty(
        Collection $wrappers
    ): MixedBattleParty;

    public function createColonyDefendingBattleParty(
        ShipInterface $leader
    ): ColonyDefendingBattleParty;
}
