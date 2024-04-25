<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;

class CallForSupportBehaviour implements PirateBehaviourInterface
{
    private PirateCreationInterface $pirateCreation;

    private LoggerUtilInterface $logger;

    public function __construct(
        PirateCreationInterface $pirateCreation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->pirateCreation = $pirateCreation;

        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $pirateReaction->react(
            $this->pirateCreation->createPirateFleet($leadShip),
            PirateReactionTriggerEnum::ON_SUPPORT_CALL
        );
    }
}
