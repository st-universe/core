<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ReactivateShipRepair;

use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ReactivateShipRepair implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REACTIVATE_REPAIR';

    public function __construct(
        private readonly ColonyLoaderInterface $colonyLoader,
        private readonly ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyFunctionManagerInterface $colonyFunctionManager,
        private readonly RepairUtilInterface $repairUtil
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $fieldId = request::indInt('repair_fid');
        if ($fieldId === 0) {
            $fid = request::indInt('fid');
            $field = $this->planetFieldRepository->find($fid);

            if ($field !== null && $field->getHost()->getId() === $colony->getId()) {
                $fieldId = $field->getFieldId();
            } else {
                // Backward compatibility for old links that passed the field index as `fid`
                $fieldId = $fid;
            }
        }

        $jobs = $this->colonyShipRepairRepository->getByColonyField($colony->getId(), $fieldId);
        if ($jobs === []) {
            return;
        }

        $field = $this->planetFieldRepository->getByColonyAndFieldIndex($colony->getId(), $fieldId);
        $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::REPAIR_SHIPYARD);
        $activeSlotCount = $isRepairStationBonus ? 2 : 1;
        $time = time();

        foreach ($jobs as $index => $job) {
            $job->setIsStopped(false);

            if ($index >= $activeSlotCount) {
                $job->setStopDate(0);
                continue;
            }

            if ($field === null || !$field->isActive()) {
                $job->setStopDate(0);
                continue;
            }

            if ($job->getFinishTime() > 0 && $job->getStopDate() > 0) {
                $job->setFinishTime($job->getFinishTime() + ($time - $job->getStopDate()));
            } elseif ($job->getFinishTime() === 0) {
                $job->setFinishTime($time + $this->repairUtil->getPassiveRepairStepDuration($job->getShip()));
            }

            $job->setStopDate(0);
        }

        $game->getInfo()->addInformation('Die Reparaturwarteschlange wurde reaktiviert');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
