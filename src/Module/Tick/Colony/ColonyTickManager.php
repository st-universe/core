<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Colony;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class ColonyTickManager implements ColonyTickManagerInterface
{
    public const LOCKFILE_DIR = '/var/tmp/';

    private $colonyTick;

    private $colonyShipRepairRepository;

    private $crewCreator;

    private $crewTrainingRepository;

    public function __construct(
        ColonyTickInterface $colonyTick,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        CrewCreatorInterface $crewCreator,
        CrewTrainingRepositoryInterface $crewTrainingRepository
    ) {
        $this->colonyTick = $colonyTick;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->crewCreator = $crewCreator;
        $this->crewTrainingRepository = $crewTrainingRepository;
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
        $colonyList = Colony::getListBy('user_id IN (SELECT id FROM stu_user WHERE user_id!=' . USER_NOONE . ' AND tick=' . $tickId . ')');

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
            $obj->getShip()->save();
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