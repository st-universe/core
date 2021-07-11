<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ColonyTickManager implements ColonyTickManagerInterface
{
    public const LOCKFILE_DIR = '/var/tmp/';

    private ColonyTickInterface $colonyTick;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private CrewCreatorInterface $crewCreator;

    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CommodityRepositoryInterface $commodityRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ColonyTickInterface $colonyTick,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        CrewCreatorInterface $crewCreator,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        PrivateMessageSenderInterface $privateMessageSender,
        CommodityRepositoryInterface $commodityRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->colonyTick = $colonyTick;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->crewCreator = $crewCreator;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->commodityRepository = $commodityRepository;
        $this->entityManager = $entityManager;
    }

    public function work(int $tickId): void
    {
        throw new Exception("verbrennt die Huxe!");

        $this->setLock($tickId);
        $this->colonyLoop($tickId);
        $this->proceedCrewTraining($tickId);
        $this->repairShips($tickId);
        $this->clearLock($tickId);

        $this->entityManager->flush();
    }

    private function colonyLoop(int $tickId): void
    {
        $commodityArray = $this->commodityRepository->getAll();
        $colonyList = $this->colonyRepository->getByTick($tickId);

        foreach ($colonyList as $colony) {
            //echo "Processing Colony ".$colony->getId()." at ".microtime()."\n";

            //handle colony only if vacation mode not active
            if (!$colony->getUser()->isVacationRequestOldEnough()) {
                $this->colonyTick->work($colony, $commodityArray);
            }
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
            if (!$obj->getColony()->hasActiveBuildingWithFunction(BuildingEnum::BUILDING_FUNCTION_ACADEMY)) {
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

            $ship = $obj->getShip();
            $colony = $obj->getColony();

            if (!$obj->getField()->isActive()) {
                continue;
            }
            $ship->setHuell($ship->getHuell() + $ship->getRepairRate());

            //repair ship systems
            $damagedSystems = $ship->getDamagedSystems();
            if (!empty($damagedSystems)) {
                $firstSystem = $damagedSystems[0];
                $firstSystem->setStatus(100);

                if ($ship->getCrewCount() > 0) {
                    $firstSystem->setMode($this->shipSystemManager->lookupSystem($firstSystem->getSystemType())->getDefaultMode());
                }

                // maximum of two systems get repaired
                if (count($damagedSystems) > 1) {
                    $secondSystem = $damagedSystems[1];
                    $secondSystem->setStatus(100);

                    if ($ship->getCrewCount() > 0) {
                        $secondSystem->setMode($this->shipSystemManager->lookupSystem($secondSystem->getSystemType())->getDefaultMode());
                    }
                }
            }

            if (!$ship->canBeRepaired()) {
                $ship->setHuell($ship->getMaxHuell());
                $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

                $this->colonyShipRepairRepository->delete($obj);

                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $ship->getUserId(),
                    sprintf(
                        "Die Reparatur der %s wurde in Sektor %s bei der Kolonie %s des Spielers %s fertiggestellt",
                        $ship->getName(),
                        $ship->getSectorString(),
                        $colony->getName(),
                        $colony->getUser()->getName()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );

                if ($ship->getUserId() != $colony->getUserId()) {
                    $this->privateMessageSender->send(
                        GameEnum::USER_NOONE,
                        $colony->getUserId(),
                        sprintf(
                            "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der Kolonie %s fertiggestellt",
                            $ship->getName(),
                            $ship->getUser()->getName(),
                            $ship->getSectorString(),
                            $colony->getName()
                        ),
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
                    );
                }
            }
            $this->shipRepository->save($ship);
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
