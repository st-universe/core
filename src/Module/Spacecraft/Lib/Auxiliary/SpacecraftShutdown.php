<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class SpacecraftShutdown implements SpacecraftShutdownInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private LeaveFleetInterface $leaveFleet,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ShipUndockingInterface $shipUndocking
    ) {}

    #[\Override]
    public function shutdown(SpacecraftWrapperInterface $wrapper, bool $doLeaveFleet = false): void
    {
        $this->spacecraftSystemManager->deactivateAll($wrapper);

        $spacecraft = $wrapper->get();

        if ($doLeaveFleet && $spacecraft instanceof Ship) {
            $this->leaveFleet->leaveFleet($spacecraft);
        }
        if ($spacecraft instanceof Station) {
            $this->shipUndocking->undockAllDocked($spacecraft);
        }
        if ($spacecraft->getState()->isActiveState()) {
            $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::NONE);
        }

        if ($spacecraft->hasComputer()) {
            $wrapper->getComputerSystemDataMandatory()->setAlertStateGreen()->update();
        }
        $this->spacecraftRepository->save($spacecraft);
    }
}
