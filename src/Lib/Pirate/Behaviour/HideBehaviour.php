<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

class HideBehaviour implements PirateBehaviourInterface
{
    private StarSystemRepositoryInterface $starSystemRepository;

    private PirateNavigationInterface $pirateNavigation;

    private LoggerUtilInterface $logger;

    public function __construct(
        StarSystemRepositoryInterface $starSystemRepository,
        PirateNavigationInterface $pirateNavigation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->starSystemRepository = $starSystemRepository;
        $this->pirateNavigation = $pirateNavigation;

        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $hideSystems = $this->starSystemRepository->getPirateHides($leadShip);
        if (empty($hideSystems)) {
            $this->logger->log('    no hide system in reach');
            return;
        }

        shuffle($hideSystems);
        $closestHideSystem = current($hideSystems);

        $this->pirateNavigation->navigateToTarget($fleet, $closestHideSystem);
    }
}
