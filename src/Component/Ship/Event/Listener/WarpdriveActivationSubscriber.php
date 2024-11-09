<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Event\Listener;

use Stu\Component\Ship\Event\WarpdriveActivationEvent;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;

/**
 * Subscribes to events related to diplomatic relations proposals
 */
final class WarpdriveActivationSubscriber
{
    public function __construct(
        private TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        private ShipStateChangerInterface $shipStateChanger,
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
        $ship = $wrapper->get();

        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);
        $this->shipUndocking->undockAllDocked($ship);

        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if (
            $tractoredShipWrapper !== null
            && $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShipWrapper->get(), $this->game)
        ) {
            $this->shipStateChanger->changeShipState($tractoredShipWrapper, ShipStateEnum::SHIP_STATE_NONE);
        }
    }
}
