<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class BattlePartyFactory implements BattlePartyFactoryInterface
{
    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly StuRandom $stuRandom
    ) {}

    #[Override]
    public function createAlertStateBattleParty(
        SpacecraftWrapperInterface $leaderWrapper
    ): AlertStateBattleParty {
        return new AlertStateBattleParty($leaderWrapper, $this->stuRandom);
    }

    #[Override]
    public function createIncomingBattleParty(
        SpacecraftWrapperInterface $leaderWrapper
    ): IncomingBattleParty {
        return new IncomingBattleParty($leaderWrapper, $this->stuRandom);
    }

    #[Override]
    public function createRoundBasedBattleParty(
        BattlePartyInterface $battleParty
    ): RoundBasedBattleParty {
        return new RoundBasedBattleParty($battleParty, $this->spacecraftRepository, $this->stuRandom);
    }

    #[Override]
    public function createAttackingBattleParty(
        SpacecraftWrapperInterface|FleetWrapperInterface $wrapper,
        bool $isAttackingShieldsOnly
    ): AttackingBattleParty {
        return new AttackingBattleParty($wrapper, $this->stuRandom, $isAttackingShieldsOnly);
    }

    #[Override]
    public function createAttackedBattleParty(
        SpacecraftWrapperInterface $wrapper
    ): AttackedBattleParty {
        return new AttackedBattleParty($wrapper, $this->stuRandom);
    }

    #[Override]
    public function createSingletonBattleParty(
        SpacecraftWrapperInterface $wrapper
    ): SingletonBattleParty {
        return new SingletonBattleParty($wrapper, $this->stuRandom);
    }

    #[Override]
    public function createMixedBattleParty(
        Collection $wrappers
    ): MixedBattleParty {
        return new MixedBattleParty($wrappers, $this->stuRandom);
    }

    #[Override]
    public function createColonyDefendingBattleParty(
        Ship $leader
    ): ColonyDefendingBattleParty {
        return new ColonyDefendingBattleParty(
            $this->spacecraftWrapperFactory->wrapShip($leader), $this->stuRandom
        );
    }
}
