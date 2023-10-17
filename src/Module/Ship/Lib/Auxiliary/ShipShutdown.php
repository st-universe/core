<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Auxiliary;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipShutdown implements ShipShutdownInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private LeaveFleetInterface $leaveFleet;

    private ShipStateChangerInterface $shipStateChanger;

    private ShipUndockingInterface $shipUndocking;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        LeaveFleetInterface $leaveFleet,
        ShipStateChangerInterface $shipStateChanger,
        ShipUndockingInterface $shipUndocking
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->leaveFleet = $leaveFleet;
        $this->shipStateChanger = $shipStateChanger;
        $this->shipUndocking = $shipUndocking;
    }

    public function shutdown(ShipWrapperInterface $wrapper): void
    {
        $this->shipSystemManager->deactivateAll($wrapper);

        $ship = $wrapper->get();

        $this->leaveFleet->leaveFleet($ship);
        $this->shipUndocking->undockAllDocked($ship);
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        $ship->setAlertStateGreen();
        $ship->setDockedTo(null);
        $this->shipRepository->save($ship);
    }
}
