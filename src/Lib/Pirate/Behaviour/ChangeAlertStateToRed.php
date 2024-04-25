<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;

class ChangeAlertStateToRed implements PirateBehaviourInterface
{
    public function action(FleetWrapperInterface $fleet): void
    {
        $fleet = $fleet->get();

        foreach ($fleet->getShips() as $ship) {
            $ship->setAlertState(ShipAlertStateEnum::ALERT_RED);
        }
    }
}
