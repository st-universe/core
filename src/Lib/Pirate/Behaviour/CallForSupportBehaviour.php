<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;

class CallForSupportBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private PirateCreationInterface $pirateCreation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $supportFleet = $this->pirateCreation->createPirateFleet($leadShip);
        $this->logger->logf(
            '    created support fleet %d "%s" here %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $supportFleet->getLeadShip()->getSectorString()
        );

        $pirateReaction->react(
            $supportFleet,
            PirateReactionTriggerEnum::ON_SUPPORT_CALL
        );

        return null;
    }
}
