<?php

namespace Stu\Lib\Pirate\Behaviour;

use Override;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class SearchFriendBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private PirateNavigationInterface $pirateNavigation,
        private FightLibInterface $fightLib,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?SpacecraftInterface $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $filteredFriends = array_filter(
            $this->shipRepository->getPirateFriends($leadWrapper),
            fn(ShipInterface $friend): bool =>
            !$friend->getCondition()->isDestroyed() && $friend->isFleetLeader()
        );

        $this->logger->logf('    number of friends in reach: %d', count($filteredFriends));

        if ($filteredFriends === []) {
            return PirateBehaviourEnum::HIDE;
        }

        usort(
            $filteredFriends,
            fn(ShipInterface $a, ShipInterface $b): int =>
            $this->fightLib->calculateHealthPercentage($a) -  $this->fightLib->calculateHealthPercentage($b)
        );

        $weakestFriend = current($filteredFriends);

        $this->logger->logf(
            '    navigating from %s to weakest friend at %s',
            $leadShip->getSectorString(),
            $weakestFriend->getSectorString()
        );

        $this->reloadMinimalEps->reload($fleet, 50);

        if ($this->pirateNavigation->navigateToTarget($fleet, $weakestFriend->getLocation())) {

            $this->logger->log('    reached weakest friend, now raging');

            $pirateReaction->react(
                $fleet->get(),
                PirateReactionTriggerEnum::ON_RAGE,
                $leadShip,
                $reactionMetadata
            );
        } else {
            return PirateBehaviourEnum::CALL_FOR_SUPPORT;
        }

        return null;
    }
}
