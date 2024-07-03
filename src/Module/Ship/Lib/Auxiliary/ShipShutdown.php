<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Auxiliary;

use Override;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipShutdown implements ShipShutdownInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private ShipSystemManagerInterface $shipSystemManager, private LeaveFleetInterface $leaveFleet, private ShipStateChangerInterface $shipStateChanger, private ShipUndockingInterface $shipUndocking)
    {
    }

    #[Override]
    public function shutdown(ShipWrapperInterface $wrapper, bool $doLeaveFleet = false): void
    {
        $this->shipSystemManager->deactivateAll($wrapper);

        $ship = $wrapper->get();

        if ($doLeaveFleet) {
            $this->leaveFleet->leaveFleet($ship);
        }
        $this->shipUndocking->undockAllDocked($ship);
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        $ship->setAlertStateGreen();
        $ship->setDockedTo(null);
        $this->shipRepository->save($ship);
    }
}
