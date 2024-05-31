<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class ChangeAlertStateToRed implements PirateBehaviourInterface
{
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum {
        $fleet = $fleet->get();

        foreach ($fleet->getShips() as $ship) {
            $ship->setAlertState(ShipAlertStateEnum::ALERT_RED);
        }

        return null;
    }
}
