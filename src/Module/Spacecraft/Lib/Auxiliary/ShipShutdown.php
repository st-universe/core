<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShipShutdown implements ShipShutdownInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private LeaveFleetInterface $leaveFleet,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ShipUndockingInterface $shipUndocking
    ) {}

    #[Override]
    public function shutdown(SpacecraftWrapperInterface $wrapper, bool $doLeaveFleet = false): void
    {
        $this->spacecraftSystemManager->deactivateAll($wrapper);

        $spacecraft = $wrapper->get();

        if ($doLeaveFleet && $spacecraft instanceof ShipInterface) {
            $this->leaveFleet->leaveFleet($spacecraft);
        }
        if ($spacecraft instanceof StationInterface) {
            $this->shipUndocking->undockAllDocked($spacecraft);
        }

        $currentState = $spacecraft->getState();
        if ($currentState != SpacecraftStateEnum::SHIP_STATE_RETROFIT && $currentState != SpacecraftStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            $this->spacecraftStateChanger->changeShipState($wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
        }

        $spacecraft->setAlertStateGreen();
        $this->spacecraftRepository->save($spacecraft);
    }
}
