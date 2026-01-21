<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

class ChangeAlertStateToRed implements PirateBehaviourInterface
{
    #[\Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        foreach ($fleet->getShipWrappers() as $wrapper) {
            $spacecraft = $wrapper->get();

            if (
                !$spacecraft->getCondition()->isDestroyed()
                && $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)
            ) {
                $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
            }
        }

        return null;
    }
}
