<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Event\Listener;

use Stu\Component\Spacecraft\Event\WarpdriveActivationEvent;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Orm\Entity\StationInterface;

/**
 * Subscribes to events related to diplomatic relations proposals
 */
final class WarpdriveActivationSubscriber
{
    public function __construct(
        private TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ShipUndockingInterface $shipUndocking,
        private GameControllerInterface $game
    ) {}

    /**
     * Reacts on warpdrive activation events
     */
    public function onWarpdriveActivation(
        WarpdriveActivationEvent $event
    ): void {

        $wrapper = $event->getWrapper();
        $spacecraft = $wrapper->get();

        $this->spacecraftStateChanger->changeShipState($wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
        if ($spacecraft instanceof StationInterface) {
            $this->shipUndocking->undockAllDocked($spacecraft);
        }

        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if (
            $tractoredShipWrapper !== null
            && $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShipWrapper->get(), $this->game)
        ) {
            $this->spacecraftStateChanger->changeShipState($tractoredShipWrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
        }
    }
}
