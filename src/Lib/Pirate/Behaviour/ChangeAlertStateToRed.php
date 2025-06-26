<?php

namespace Stu\Lib\Pirate\Behaviour;

use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

class ChangeAlertStateToRed implements PirateBehaviourInterface
{
    #[Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        foreach ($fleet->getShipWrappers() as $wrapper) {
            $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
        }

        return null;
    }
}
