<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;

class DeactivateShieldsBehaviour implements PirateBehaviourInterface
{
    public function __construct(private ShipSystemManagerInterface $shipSystemManager)
    {
    }
    public function action(FleetWrapperInterface $fleetWrapper, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $ship = $wrapper->get();
            if ($ship->getShield() === $ship->getMaxShield()) {
                continue;
            }

            if ($ship->getStorage()->isEmpty()) {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS, true);
            }
        }

        return null;
    }
}
