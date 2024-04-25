<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;

class CallForSupportBehaviour implements PirateBehaviourInterface
{
    private PirateCreationInterface $pirateCreation;

    public function __construct(
        PirateCreationInterface $pirateCreation
    ) {
        $this->pirateCreation = $pirateCreation;
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
