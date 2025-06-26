<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class BattlePartyFactory implements BattlePartyFactoryInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function createAlertStateBattleParty(
        SpacecraftWrapperInterface $leaderWrapper
    ): AlertStateBattleParty {
        return new AlertStateBattleParty($leaderWrapper);
    }

    #[Override]
    public function createIncomingBattleParty(
        SpacecraftWrapperInterface $leaderWrapper
    ): IncomingBattleParty {
        return new IncomingBattleParty($leaderWrapper);
    }

    #[Override]
    public function createRoundBasedBattleParty(
        BattlePartyInterface $battleParty
    ): RoundBasedBattleParty {
        return new RoundBasedBattleParty($battleParty, $this->spacecraftRepository);
    }

    #[Override]
    public function createAttackingBattleParty(
        SpacecraftWrapperInterface|FleetWrapperInterface $wrapper,
        bool $isAttackingShieldsOnly
    ): AttackingBattleParty {
        return new AttackingBattleParty($wrapper, $isAttackingShieldsOnly);
    }

    #[Override]
    public function createAttackedBattleParty(
        SpacecraftWrapperInterface $wrapper
    ): AttackedBattleParty {
        return new AttackedBattleParty($wrapper);
    }

    #[Override]
    public function createSingletonBattleParty(
        SpacecraftWrapperInterface $wrapper
    ): SingletonBattleParty {
        return new SingletonBattleParty($wrapper);
    }

    #[Override]
    public function createMixedBattleParty(
        Collection $wrappers
    ): MixedBattleParty {
        return new MixedBattleParty($wrappers);
    }

    #[Override]
    public function createColonyDefendingBattleParty(
        Ship $leader
    ): ColonyDefendingBattleParty {
        return new ColonyDefendingBattleParty(
            $this->spacecraftWrapperFactory->wrapShip($leader)
        );
    }
}
