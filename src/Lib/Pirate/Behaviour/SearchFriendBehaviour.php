<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class SearchFriendBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private PirateNavigationInterface $pirateNavigation,
        private PirateReactionInterface $pirateReaction,
        private FightLibInterface $fightLib,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->pirateNavigation = $pirateNavigation;

        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $friends = $this->shipRepository->getPirateFriends($leadShip);

        $this->logger->logf('    number of friends in reach: %d', count($friends));

        if (empty($friends)) {
            return PirateBehaviourEnum::HIDE;
        }

        usort(
            $friends,
            fn (ShipInterface $a, ShipInterface $b) =>
            $this->fightLib->calculateHealthPercentage($a) -  $this->fightLib->calculateHealthPercentage($b)
        );

        $weakestFriend = current($friends);

        $this->logger->logf('    navigating to weakest friend at %s', $weakestFriend->getSectorString());

        if ($this->pirateNavigation->navigateToTarget($fleet, $weakestFriend->getCurrentMapField())) {

            $this->logger->log('    reached weakest friend, now raging');

            $this->pirateReaction->react(
                $fleet->get(),
                PirateReactionTriggerEnum::ON_RAGE
            );
        }

        return null;
    }
}
