<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ColonyTickManager implements ColonyTickManagerInterface
{
    public const LOCKFILE_DIR = '/var/tmp/';

    private $colonyTick;

    private $colonyShipRepairRepository;

    private $crewCreator;

    private $crewTrainingRepository;

    private $colonyRepository;

    private $shipRepository;

    public function __construct(
        ColonyTickInterface $colonyTick,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        CrewCreatorInterface $crewCreator,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->colonyTick = $colonyTick;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->crewCreator = $crewCreator;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
    }

    public function work(int $tickId): void
    {
        $this->setLock($tickId);
        $this->colonyLoop($tickId);
        $this->proceedCrewTraining($tickId);
        $this->repairShips($tickId);
        $this->clearLock($tickId);
    }

    private function colonyLoop(int $tickId): void
    {
        $colonyList = $this->colonyRepository->getByTick($tickId);

        foreach ($colonyList as $colony) {
            //echo "Processing Colony ".$colony->getId()." at ".microtime()."\n";
            $this->colonyTick->work($colony);
        }
    }

    private function proceedCrewTraining(int $tickId): void
    {
        $user = array();
        foreach ($this->crewTrainingRepository->getByTick($tickId) as $obj) {
            if (!isset($user[$obj->getUserId()])) {
                $user[$obj->getUserId()] = 0;
            }
            if ($user[$obj->getUserId()] >= $obj->getUser()->getTrainableCrewCountMax()) {
                continue;
            }
            if ($obj->getUser()->getGlobalCrewLimit() - $obj->getUser()->getUsedCrewCount() - $obj->getUser()->getFreeCrewCount() <= 0) {
                continue;
            }
            if (!$obj->getColony()->hasActiveBuildingWithFunction(BUILDING_FUNCTION_ACADEMY)) {
                continue;
            }
            $this->crewCreator->create((int) $obj->getUserId());

            $this->crewTrainingRepository->delete($obj);
            $user[$obj->getUserId()]++;
        }
    }

    private function repairShips(int $tickId): void
    {
        foreach ($this->colonyShipRepairRepository->getMostRecentJobs($tickId) as $obj) {
            if (!$obj->getField()->isActive()) {
                continue;
            }
            $obj->getShip()->setHuell($obj->getShip()->getHuell() + $obj->getShip()->getRepairRate());
            if (!$obj->getShip()->canBeRepaired()) {
                $obj->getShip()->setHuell($obj->getShip()->getMaxHuell());
                $obj->getShip()->setState(SHIP_STATE_NONE);

                $this->colonyShipRepairRepository->delete($obj);
            }
            $this->shipRepository->save($obj->getShip());
        }
    }

    private function setLock(int $tickId): void
    {
        @touch(self::LOCKFILE_DIR . $tickId . '.lock');
    }

    private function clearLock(int $tickId): void
    {
        @unlink(self::LOCKFILE_DIR . $tickId . '.lock');
    }

}