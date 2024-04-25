<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;

class CallForSupportBehaviour implements PirateBehaviourInterface
{
    private PirateCreationInterface $pirateCreation;

    private PirateLoggerInterface $logger;

    public function __construct(
        PirateCreationInterface $pirateCreation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->pirateCreation = $pirateCreation;

        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): void
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
    }
}
