<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ReactivateShipRepair;

use request;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class ReactivateShipRepair implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REACTIVATE_REPAIR';

    public function __construct(
        private readonly StationLoaderInterface $stationLoader,
        private readonly StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private readonly StationUtilityInterface $stationUtility,
        private readonly RepairUtilInterface $repairUtil
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $station = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId(),
            false,
            false
        );

        $jobs = $this->stationShipRepairRepository->getByStation($station->getId());
        if ($jobs === []) {
            return;
        }

        $time = time();
        $canRepair = $this->stationUtility->canRepairShips($station);

        foreach ($jobs as $index => $job) {
            $job->setIsStopped(false);

            if ($index > 0 || !$canRepair) {
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
