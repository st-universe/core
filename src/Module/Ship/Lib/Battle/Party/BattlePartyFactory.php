<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class BattlePartyFactory implements BattlePartyFactoryInterface
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
    }

    public function createAlertStateBattleParty(
        ShipWrapperInterface $leaderWrapper
    ): AlertStateBattleParty {
        return new AlertStateBattleParty($leaderWrapper);
    }

    public function createIncomingBattleParty(
        ShipWrapperInterface $leaderWrapper
    ): IncomingBattleParty {
        return new IncomingBattleParty($leaderWrapper);
    }

    public function createRoundBasedBattleParty(
        BattlePartyInterface $battleParty
    ): RoundBasedBattleParty {
        return new RoundBasedBattleParty($battleParty, $this->shipRepository);
    }

    public function createAttackingBattleParty(
        ShipWrapperInterface|FleetWrapperInterface $wrapper
    ): AttackingBattleParty {
        return new AttackingBattleParty($wrapper);
    }

    public function createAttackedBattleParty(
        ShipWrapperInterface $wrapper
    ): AttackedBattleParty {
        return new AttackedBattleParty($wrapper);
    }

    public function createSingletonBattleParty(
        ShipWrapperInterface $wrapper
    ): SingletonBattleParty {
        return new SingletonBattleParty($wrapper);
    }

    public function createMixedBattleParty(
        Collection $wrappers
    ): MixedBattleParty {
        return new MixedBattleParty($wrappers);
    }

    public function createColonyDefendingBattleParty(
        ShipInterface $leader
    ): ColonyDefendingBattleParty {
        return new ColonyDefendingBattleParty(
            $this->shipWrapperFactory->wrapShip($leader)
        );
    }
}
