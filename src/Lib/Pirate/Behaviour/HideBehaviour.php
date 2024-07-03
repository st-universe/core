<?php

namespace Stu\Lib\Pirate\Behaviour;

use Override;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

class HideBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private StarSystemRepositoryInterface $starSystemRepository,
        private PirateNavigationInterface $pirateNavigation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum {

        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $hideSystems = $this->starSystemRepository->getPirateHides($leadShip);
        if ($hideSystems === []) {
            $this->logger->log('    no hide system in reach');
            return PirateBehaviourEnum::RAGE;
        }

        shuffle($hideSystems);
        $closestHideSystem = current($hideSystems);

        $this->pirateNavigation->navigateToTarget($fleet, $closestHideSystem);

        return null;
    }
}
