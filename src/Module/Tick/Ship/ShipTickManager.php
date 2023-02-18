<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Specials\AdventCycleInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipTickManager implements ShipTickManagerInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRemoverInterface $shipRemover;

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private CrewRepositoryInterface $crewRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private AlertRedHelperInterface $alertRedHelper;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private StationShipRepairRepositoryInterface $stationShipRepairRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    private RepairUtilInterface $repairUtil;

    private AdventCycleInterface $adventCycle;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private EntryCreatorInterface $entryCreator;

    private LoggerUtilInterface $loggerUtil;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyFunctionManagerInterface $colonyFunctionManager;
    private CrewLimitCalculatorInterface $crewLimitCalculator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        CrewRepositoryInterface $crewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TradePostRepositoryInterface $tradePostRepository,
        ShipSystemManagerInterface $shipSystemManager,
        AlertRedHelperInterface $alertRedHelper,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        StationShipRepairRepositoryInterface $stationShipRepairRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        RepairUtilInterface $repairUtil,
        AdventCycleInterface $adventCycle,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        EntryCreatorInterface $entryCreator,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        CrewLimitCalculatorInterface $crewLimitCalculator,
        ColonyLibFactoryInterface $colonyLibFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->crewRepository = $crewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->alertRedHelper = $alertRedHelper;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->stationShipRepairRepository = $stationShipRepairRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->moduleQueueRepository = $moduleQueueRepository;
        $this->repairUtil = $repairUtil;
        $this->adventCycle = $adventCycle;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->entryCreator = $entryCreator;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->crewLimitCalculator = $crewLimitCalculator;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function work(): void
    {
        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->checkForCrewLimitation();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\tcheckForCrewLimitation, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->handleEscapePods();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\thandleEscapePods, seconds: %F", $endTime - $startTime));
        }
        //$this->loggerUtil->init();

        //spare parts and system components are generated by ship tick, to avoid dead locks
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->proceedSpareParts();
        $this->repairShipsOnColonies(1);
        $this->repairShipsOnStations();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\tRepairStuff, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        foreach ($this->shipRepository->getPlayerShipsForTick() as $ship) {
            //echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";

            $this->shipTick->work($this->shipWrapperFactory->wrapShip($ship));
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\tshipTick, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->handleNPCShips();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\thandleNPCShips, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->lowerTrumfieldHull();
        $this->lowerOrphanizedTradepostHull();
        $this->lowerStationConstructionHull();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\tloweringTrumfieldConstruction, seconds: %F", $endTime - $startTime));
        }

        //do optional advent stuff
        $this->adventCycle->cycle();
    }

    private function handleEscapePods(): void
    {
        $escapedToColonies = [];

        foreach ($this->shipRepository->getEscapePods() as $escapePod) {
            if ($escapePod->getCrewCount() === 0) {
                $this->shipRemover->remove($escapePod);
            }

            if ($escapePod->getStarsystemMap() !== null) {
                $colony = $escapePod->getStarsystemMap()->getColony();

                if ($colony !== null) {
                    $count = $this->transferOwnCrewToColony($escapePod, $colony);

                    if ($count > 0) {
                        if (array_key_exists($colony->getId(), $escapedToColonies)) {
                            $oldCount = $escapedToColonies[$colony->getId()][1];

                            $escapedToColonies[$colony->getId()][1] = $oldCount +  $count;
                        } else {
                            $escapedToColonies[$colony->getId()] = [$colony, $count];
                        }
                    }
                }
            }
        }

        foreach ($escapedToColonies as [$colony, $count]) {
            $msg = sprintf(_('%d deiner Crewman sind aus Fluchtkapseln auf deiner Kolonie %s gelandet'), $count, $colony->getName());
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $colony->getUser()->getId(),
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }
    }

    private function transferOwnCrewToColony(ShipInterface $escapePod, ColonyInterface $colony): int
    {
        $count = 0;

        foreach ($escapePod->getCrewlist() as $crewAssignment) {
            $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony,
                $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction()
            )->getFreeAssignmentCount();

            if ($freeAssignmentCount === 0) {
                break;
            }
            if ($crewAssignment->getUser() !== $colony->getUser()) {
                continue;
            }
            $count++;
            $crewAssignment->setShip(null);
            $crewAssignment->setSlot(null);
            $crewAssignment->setColony($colony);
            $escapePod->getCrewlist()->removeElement($crewAssignment);
            $colony->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);
        }

        return $count;
    }

    private function checkForCrewLimitation(): void
    {
        $userList = $this->userRepository->getNonNpcList();

        foreach ($userList as $user) {
            //only handle user that are not on vacation
            if ($user->isVacationRequestOldEnough()) {
                continue;
            }

            $userId = $user->getId();

            $crewLimit = $this->crewLimitCalculator->getGlobalCrewLimit($user);
            $crewOnColonies = $this->shipCrewRepository->getAmountByUserOnColonies($user->getId());
            $crewOnShips = $this->shipCrewRepository->getAmountByUserOnShips($user);
            $crewAtTradeposts = $this->shipCrewRepository->getAmountByUserAtTradeposts($user);

            $crewToQuit = max(0, $crewOnColonies + $crewOnShips + $crewAtTradeposts - $crewLimit);

            //mutiny order: colonies, ships, tradeposts, escape pods
            if ($crewToQuit > 0 && $crewOnColonies > 0) {
                $crewToQuit -= $this->letColonyAssignmentsQuit($userId, $crewToQuit);
            }
            if ($crewToQuit > 0 && $crewOnShips > 0) {
                $crewToQuit -= $this->letShipAssignmentsQuit($userId, $crewToQuit);
            }
            if ($crewToQuit > 0 && $crewAtTradeposts > 0) {
                $crewToQuit -= $this->letTradepostAssignmentsQuit($userId, $crewToQuit);
            }
            if ($crewToQuit > 0) {
                $this->letEscapePodAssignmentsQuit($userId, $crewToQuit);
            }
        }
    }

    private function letColonyAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->shipCrewRepository->getByUserAtColonies($userId) as $crewAssignment) {
            if ($amount === $crewToQuit) {
                break;
            }

            $amount++;
            $this->crewRepository->delete($crewAssignment->getCrew());
        }

        if ($amount > 0) {
            $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d Crewman ihren Dienst auf deinen Kolonien quittiert'), $amount);
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $userId,
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }

        return $amount;
    }

    private function letTradepostAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->shipCrewRepository->getByUserAtTradeposts($userId) as $crewAssignment) {
            if ($amount === $crewToQuit) {
                break;
            }

            $amount++;
            $this->crewRepository->delete($crewAssignment->getCrew());
        }

        if ($amount > 0) {
            $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d deiner Crewman auf Handelsposten ihren Dienst quittiert'), $amount);
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $userId,
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
            );
        }

        return $amount;
    }

    private function letEscapePodAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->shipCrewRepository->getByUserOnEscapePods($userId) as $crewAssignment) {
            if ($amount === $crewToQuit) {
                break;
            }

            $amount++;
            $this->crewRepository->delete($crewAssignment->getCrew());
        }

        if ($amount > 0) {
            $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d deiner Crewman auf Fluchtkapseln ihren Dienst quittiert'), $amount);
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $userId,
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
            );
        }

        return $amount;
    }

    private function letShipAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        $wipedShipIds = [];

        while ($amount < $crewToQuit) {

            $randomShipId = $this->shipRepository->getRandomShipIdWithCrewByUser($userId);

            //if no more ships available
            if ($randomShipId === null) {
                break;
            }

            //if ship already wiped, go to next
            if (in_array($randomShipId, $wipedShipIds)) {
                continue;
            }

            //wipe ship crew
            $wipedShipsIds[] = $randomShipId;
            $amount += $this->letCrewQuit($randomShipId, $userId);
        }

        return $amount;
    }

    private function letCrewQuit(int $randomShipId, int $userId): ?int
    {
        $randomShip = $this->shipRepository->find($randomShipId);
        $doAlertRedCheck = $randomShip->getWarpState() || $randomShip->getCloakState();
        //deactivate ship
        $this->shipSystemManager->deactivateAll($this->shipWrapperFactory->wrapShip($randomShip));
        $randomShip->setAlertStateGreen();

        $this->shipRepository->save($randomShip);

        $crewArray = [];
        foreach ($randomShip->getCrewlist() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
        }
        $randomShip->getCrewlist()->clear();

        //remove crew
        $this->shipCrewRepository->truncateByShip($randomShipId);
        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }

        $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits hat die Crew der %s gemeutert und das Schiff verlassen'), $randomShip->getName());
        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            $userId,
            $msg,
            $randomShip->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        //do alert red stuff
        if ($doAlertRedCheck) {
            $this->alertRedHelper->doItAll($randomShip, null);
        }

        return count($crewArray);
    }

    private function lowerTrumfieldHull(): void
    {
        foreach ($this->shipRepository->getDebrisFields() as $ship) {
            $lower = rand(5, 15);
            if ($ship->getHull() <= $lower) {
                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function lowerOrphanizedTradepostHull(): void
    {
        foreach ($this->tradePostRepository->getByUser(GameEnum::USER_NOONE) as $tradepost) {
            $ship = $tradepost->getShip();

            $lower = (int)ceil($ship->getMaxHull() / 100);

            if ($ship->getHull() <= $lower) {
                $this->shipRemover->destroy($this->shipWrapperFactory->wrapShip($ship));

                $this->entryCreator->addStationEntry(
                    'Der verlassene Handelsposten in Sektor ' . $ship->getSectorString() . ' ist zerfallen',
                    $ship->getUser()->getId()
                );
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function lowerStationConstructionHull(): void
    {
        foreach ($this->shipRepository->getStationConstructions() as $ship) {
            $lower = rand(5, 15);
            if ($ship->getHull() <= $lower) {

                $msg = sprintf(_('Dein Konstrukt bei %s war zu lange ungenutzt und ist daher zerfallen'), $ship->getSectorString());
                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    $msg,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
                );

                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function handleNPCShips(): void
    {
        // @todo
        foreach ($this->shipRepository->getNpcShipsForTick() as $ship) {
            $wrapper = $this->shipWrapperFactory->wrapShip($ship);
            $epsSystem = $wrapper->getEpsSystemData();

            if ($epsSystem !== null) {
                $eps = (int) ceil($ship->getReactorOutput() - $wrapper->getEpsUsage());
                if ($eps + $epsSystem->getEps() > $epsSystem->getMaxEps()) {
                    $eps = $epsSystem->getMaxEps() - $epsSystem->getEps();
                }
                $epsSystem->setEps($epsSystem->getEps() + $eps)->update();
            }
        }
    }

    private function proceedSpareParts(): void
    {
        foreach ($this->moduleQueueRepository->findAll() as $queue) {
            $buildingFunction = $queue->getBuildingFunction();

            if (
                $buildingFunction === BuildingEnum::BUILDING_FUNCTION_FABRICATION_HALL ||
                $buildingFunction === BuildingEnum::BUILDING_FUNCTION_TECH_CENTER
            ) {
                $colony = $queue->getColony();

                if ($this->colonyFunctionManager->hasActiveFunction($colony, $buildingFunction)) {
                    $this->colonyStorageManager->upperStorage(
                        $colony,
                        $queue->getModule()->getCommodity(),
                        $queue->getAmount()
                    );

                    $this->privateMessageSender->send(
                        GameEnum::USER_NOONE,
                        $colony->getUser()->getId(),
                        sprintf(
                            _('Es wurden %d %s hergestellt'),
                            $queue->getAmount(),
                            $queue->getModule()->getName()
                        ),
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
                    );

                    $this->moduleQueueRepository->delete($queue);
                }
            }
        }
    }

    private function repairShipsOnColonies(int $tickId): void
    {
        $usedShipyards = [];

        foreach ($this->colonyShipRepairRepository->getMostRecentJobs($tickId) as $obj) {

            $ship = $obj->getShip();
            $colony = $obj->getColony();

            if ($colony->isBlocked()) {
                continue;
            }

            $field = $this->planetFieldRepository->getByColonyAndFieldId(
                $obj->getColonyId(),
                $obj->getFieldId()
            );

            if ($field === null) {
                continue;
            }

            if (!$field->isActive()) {
                continue;
            }

            if (!array_key_exists($colony->getId(), $usedShipyards)) {
                $usedShipyards[$colony->getId()] = [];
            }

            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);

            //already repaired a ship on this colony field, max is one without repair station
            if (
                !$isRepairStationBonus
                && array_key_exists($field->getFieldId(), $usedShipyards[$colony->getId()])
            ) {
                continue;
            }

            $usedShipyards[$colony->getId()][$field->getFieldId()] = [$field->getFieldId()];

            if ($this->repairShipOnEntity($ship, $colony, true, $isRepairStationBonus)) {
                $this->colonyShipRepairRepository->delete($obj);
                $this->shipRepository->save($ship);
            }
        }
    }

    private function repairShipsOnStations(): void
    {
        foreach ($this->stationShipRepairRepository->getMostRecentJobs() as $obj) {

            $this->loggerUtil->log('stationRepairJobId: ' . $obj->getId());

            $ship = $obj->getShip();
            $station = $obj->getStation();

            if (!$station->hasEnoughCrew()) {
                continue;
            }

            if ($this->repairShipOnEntity($ship, $station, false, false)) {
                $this->stationShipRepairRepository->delete($obj);
                $this->shipRepository->save($ship);
            }
        }
    }

    private function repairShipOnEntity(ShipInterface $ship, $entity, bool $isColony, bool $isRepairStationBonus): bool
    {

        // check for U-Mode
        if ($entity->getUser()->isVacationRequestOldEnough()) {
            return false;
        }

        $neededParts = $this->repairUtil->determineSpareParts($ship);

        // parts stored?
        if (!$this->repairUtil->enoughSparePartsOnEntity($neededParts, $entity, $isColony, $ship)) {
            return false;
        }

        $repairFinished = false;

        $hullRepairRate = $isRepairStationBonus ? $ship->getRepairRate() * 2 : $ship->getRepairRate();
        $ship->setHuell($ship->getHull() + $hullRepairRate);
        if ($ship->getHull() > $ship->getMaxHull()) {
            $ship->setHuell($ship->getMaxHull());
        }

        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        //repair ship systems
        $damagedSystems = $wrapper->getDamagedSystems();
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

            // maximum of two additional systems get repaired
            if ($isRepairStationBonus) {
                if (count($damagedSystems) > 2) {
                    $thirdSystem = $damagedSystems[2];
                    $thirdSystem->setStatus(100);

                    if ($ship->getCrewCount() > 0) {
                        $thirdSystem->setMode($this->shipSystemManager->lookupSystem($thirdSystem->getSystemType())->getDefaultMode());
                    }
                }
                if (count($damagedSystems) > 3) {
                    $fourthSystem = $damagedSystems[3];
                    $fourthSystem->setStatus(100);

                    if ($ship->getCrewCount() > 0) {
                        $fourthSystem->setMode($this->shipSystemManager->lookupSystem($fourthSystem->getSystemType())->getDefaultMode());
                    }
                }
            }
        }

        // consume spare parts
        $this->repairUtil->consumeSpareParts($neededParts, $entity, $isColony);

        if (!$wrapper->canBeRepaired()) {
            $repairFinished = true;

            $ship->setHuell($ship->getMaxHull());
            $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

            $shipOwnerMessage = $isColony ? sprintf(
                "Die Reparatur der %s wurde in Sektor %s bei der Kolonie %s des Spielers %s fertiggestellt",
                $ship->getName(),
                $ship->getSectorString(),
                $entity->getName(),
                $entity->getUser()->getName()
            ) : sprintf(
                "Die Reparatur der %s wurde in Sektor %s von der %s %s des Spielers %s fertiggestellt",
                $ship->getName(),
                $ship->getSectorString(),
                $entity->getRump()->getName(),
                $entity->getName(),
                $entity->getUser()->getName()
            );

            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $ship->getUser()->getId(),
                $shipOwnerMessage,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );

            if ($ship->getUser()->getId() != $entity->getUserId()) {
                $entityOwnerMessage = $isColony ? sprintf(
                    "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der Kolonie %s fertiggestellt",
                    $ship->getName(),
                    $ship->getUser()->getName(),
                    $ship->getSectorString(),
                    $entity->getName()
                ) : sprintf(
                    "Die Reparatur der %s von Siedler %s wurde in Sektor %s von der %s %s fertiggestellt",
                    $ship->getName(),
                    $ship->getUser()->getName(),
                    $ship->getSectorString(),
                    $entity->getRump()->getName(),
                    $entity->getName()
                );

                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $entity->getUserId(),
                    $entityOwnerMessage,
                    $isColony ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY :
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
                );
            }
        }
        $this->shipRepository->save($ship);

        return $repairFinished;
    }
}
