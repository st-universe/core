<?php

namespace Stu\Module\Tick\Spacecraft;

use Override;
use RuntimeException;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Map\Effects\EffectHandlingInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Module\Tick\Spacecraft\ManagerComponent\ManagerComponentInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class SpacecraftTick implements SpacecraftTickInterface, ManagerComponentInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private EffectHandlingInterface $effectHandlingInterface,
        private SpacecraftLeaverInterface $spacecraftLeaver,
        private GameControllerInterface $game,
        private AstroEntryLibInterface $astroEntryLib,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private CreateDatabaseEntryInterface $createDatabaseEntry,
        private StationUtilityInterface $stationUtility,
        private RepairUtilInterface $repairUtil,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private TrackerDeviceManagerInterface $trackerDeviceManager,
        private StorageManagerInterface $storageManager,
        private LocationMiningRepositoryInterface $locationMiningRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private StorageRepositoryInterface $storageRepository,
        private InformationFactoryInterface $informationFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil(true);
    }

    #[Override]
    public function work(): void
    {
        foreach ($this->spacecraftRepository->getPlayerSpacecraftsForTick() as $spacecraft) {
            $this->workSpacecraft($this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft));
        }
    }

    #[Override]
    public function workSpacecraft(SpacecraftWrapperInterface $wrapper): void
    {
        $informationWrapper = $this->informationFactory->createInformationWrapper();

        $this->workSpacecraftInternal($wrapper, $informationWrapper);

        $this->sendMessage($wrapper->get(), $informationWrapper);
    }

    private function workSpacecraftInternal(SpacecraftWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        $spacecraft = $wrapper->get();

        $startTime = microtime(true);
        $this->effectHandlingInterface->handleSpacecraftTick($wrapper, $informationWrapper);
        $this->potentialLog($spacecraft, "marker0.1", $startTime);

        $startTime = microtime(true);

        // do construction stuff
        if ($spacecraft instanceof StationInterface && $this->doConstructionStuff($spacecraft, $informationWrapper)) {
            $this->spacecraftRepository->save($spacecraft);
            return;
        }

        $this->potentialLog($spacecraft, "marker0.2", $startTime);


        $startTime = microtime(true);
        // repair station
        if ($wrapper instanceof StationWrapperInterface && $spacecraft->getState() === SpacecraftStateEnum::REPAIR_PASSIVE) {
            $this->doRepairStation($wrapper, $informationWrapper);
        }
        $this->potentialLog($spacecraft, "marker1", $startTime);

        $startTime = microtime(true);
        // leave ship
        if ($spacecraft->getCrewCount() > 0 && !$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::LIFE_SUPPORT)) {
            $informationWrapper->addInformation('Die Lebenserhaltung ist ausgefallen:');
            $informationWrapper->addInformation($this->spacecraftLeaver->evacuate($wrapper));
            $this->potentialLog($spacecraft, "marker2", $startTime);
            return;
        }

        $startTime = microtime(true);
        $eps = $wrapper->getEpsSystemData();
        $reactor = $wrapper->getReactorWrapper();
        if ($eps === null) {
            $this->potentialLog($spacecraft, "marker3", $startTime);
            return;
        }

        $startTime = microtime(true);
        $hasEnoughCrew = $spacecraft->hasEnoughCrew();

        // not enough crew
        if (!$hasEnoughCrew) {
            $informationWrapper->addInformation('Zu wenig Crew an Bord, Schiff ist nicht voll funktionsfähig! Systeme werden deaktiviert!');

            //deactivate all systems except life support
            foreach ($this->spacecraftSystemManager->getActiveSystems($spacecraft) as $system) {
                if ($system->getSystemType() != SpacecraftSystemTypeEnum::LIFE_SUPPORT) {
                    $this->spacecraftSystemManager->deactivate($wrapper, $system->getSystemType(), true);
                }
            }
        }
        $this->potentialLog($spacecraft, "marker4", $startTime);

        $startTime = microtime(true);
        $reactorUsageForWarpdrive = $this->loadWarpdrive(
            $wrapper,
            $hasEnoughCrew
        );
        $this->potentialLog($spacecraft, "marker5", $startTime);

        $startTime = microtime(true);
        $availableEps = $this->getAvailableEps(
            $wrapper,
            $eps,
            $reactor,
            $hasEnoughCrew,
            $reactorUsageForWarpdrive
        );
        $this->potentialLog($spacecraft, "marker6", $startTime);

        $startTime = microtime(true);
        //try to save energy by reducing alert state
        if ($wrapper->getEpsUsage() > $availableEps) {
            $malus = $wrapper->getEpsUsage() - $availableEps;
            $alertUsage = $spacecraft->getAlertState()->value - 1;

            if ($alertUsage > 0) {
                $preState = $spacecraft->getAlertState();
                $reduce = (int)min($malus, $alertUsage);

                $spacecraft->setAlertState(SpacecraftAlertStateEnum::from($preState->value - $reduce));
                $informationWrapper->addInformationf(
                    'Wechsel von %s auf %s wegen Energiemangel',
                    $preState->getDescription(),
                    $spacecraft->getAlertState()->getDescription()
                );
            }
        }
        $this->potentialLog($spacecraft, "marker7", $startTime);

        $startTime = microtime(true);
        //try to save energy by deactivating systems from low to high priority
        if ($wrapper->getEpsUsage() > $availableEps) {
            $activeSystems = $this->spacecraftSystemManager->getActiveSystems($spacecraft, true);

            foreach ($activeSystems as $system) {
                $energyConsumption = $this->spacecraftSystemManager->getEnergyConsumption($system->getSystemType());
                if ($energyConsumption < 1) {
                    continue;
                }

                //echo "- eps: ".$eps." - usage: ".$wrapper->getEpsUsage()."\n";
                if ($availableEps - $wrapper->getEpsUsage() - $energyConsumption < 0) {
                    //echo "-- hit system: ".$system->getDescription()."\n";

                    $this->spacecraftSystemManager->deactivate($wrapper, $system->getSystemType(), true);

                    $wrapper->lowerEpsUsage($energyConsumption);
                    $informationWrapper->addInformationf('%s deaktiviert wegen Energiemangel', $system->getSystemType()->getDescription());

                    if ($spacecraft->getCrewCount() > 0 && $system->getSystemType() == SpacecraftSystemTypeEnum::LIFE_SUPPORT) {
                        $informationWrapper->addInformation('Die Lebenserhaltung ist ausgefallen:');
                        $informationWrapper->addInformation($this->spacecraftLeaver->evacuate($wrapper));
                        return;
                    }
                }
                if ($wrapper->getEpsUsage() <= $availableEps) {
                    break;
                }
            }
        }

        $this->potentialLog($spacecraft, "marker8", $startTime);
        $startTime = microtime(true);

        $newEps = $availableEps - $wrapper->getEpsUsage();
        $batteryReload = $spacecraft->isStation()
            && $eps->reloadBattery()
            && $newEps > $eps->getEps()
            ? min(
                (int) ceil($eps->getMaxBattery() / 10),
                $newEps - $eps->getEps(),
                $eps->getMaxBattery() - $eps->getBattery()
            ) : 0;

        $newEps -= $batteryReload;
        if ($newEps > $eps->getMaxEps()) {
            $newEps = $eps->getMaxEps();
        }


        $usedEnergy = $wrapper->getEpsUsage() + $batteryReload + ($newEps - $eps->getEps()) + $reactorUsageForWarpdrive;

        //echo "--- Generated Id ".$ship->getId()." - eps: ".$eps." - usage: ".$wrapper->getEpsUsage()." - old eps: ".$ship->getEps()." - wk: ".$wkuse."\n";
        $eps->setEps($newEps)
            ->setBattery($eps->getBattery() + $batteryReload)
            ->update();

        if ($usedEnergy > 0 && $reactor !== null) {
            $reactor->changeLoad(-$usedEnergy);
        }

        $this->potentialLog($spacecraft, "marker9", $startTime);

        $startTime = microtime(true);
        $this->checkForFinishedTakeover($spacecraft);
        $this->potentialLog($spacecraft, "marker10", $startTime);

        $startTime = microtime(true);
        $this->checkForFinishedAstroMapping($wrapper, $informationWrapper);
        $this->potentialLog($spacecraft, "marker11", $startTime);

        //update tracker status
        $startTime = microtime(true);
        $this->doTrackerDeviceStuff($wrapper);
        $this->potentialLog($spacecraft, "marker12", $startTime);

        $startTime = microtime(true);
        $this->spacecraftRepository->save($spacecraft);
        $this->potentialLog($spacecraft, "marker13", $startTime);

        $startTime = microtime(true);
        $this->doBussardCollectorStuff($wrapper, $informationWrapper);
        $this->potentialLog($spacecraft, "marker15", $startTime);

        $startTime = microtime(true);
        $this->doAggregationSystemStuff($wrapper, $informationWrapper);
        $this->potentialLog($spacecraft, "marker16", $startTime);
    }

    private function potentialLog(SpacecraftInterface $spacecraft, string $marker, float $startTime): void
    {
        $endTime = microtime(true);

        if (
            $endTime - $startTime > 0.01
        ) {
            $this->loggerUtil->log(sprintf(
                "\t\t\t%s of %d, seconds: %F",
                $marker,
                $spacecraft->getId(),
                $endTime - $startTime
            ));
        }
    }

    private function getAvailableEps(
        SpacecraftWrapperInterface $wrapper,
        EpsSystemData $eps,
        ?ReactorWrapperInterface $reactor,
        bool $hasEnoughCrew,
        int $reactorUsageForWarpdrive
    ): int {
        if ($hasEnoughCrew && $reactor !== null) {

            return $eps->getEps() + $reactor->getEpsProduction() +  $this->getCarryOver(
                $wrapper,
                $reactor,
                $reactorUsageForWarpdrive
            );
        }

        return $eps->getEps();
    }

    private function getCarryOver(
        SpacecraftWrapperInterface $wrapper,
        ReactorWrapperInterface $reactor,
        int $reactorUsageForWarpdrive
    ): int {
        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive === null || !$warpdrive->getAutoCarryOver()) {
            return 0;
        }

        return $reactor->getOutputCappedByLoad() - $reactor->getEpsProduction() - $reactorUsageForWarpdrive;
    }

    private function loadWarpdrive(SpacecraftWrapperInterface $wrapper, bool $hasEnoughCrew): int
    {
        if (!$hasEnoughCrew) {
            return 0;
        }

        $reactor = $wrapper->getReactorWrapper();
        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive === null || $reactor === null) {
            return 0;
        }

        $effectiveWarpdriveProduction = $reactor->getEffectiveWarpDriveProduction();
        if ($effectiveWarpdriveProduction === 0) {
            return 0;
        }

        $currentLoad = $warpdrive->getWarpDrive();

        $warpdrive->setWarpDrive($currentLoad + $effectiveWarpdriveProduction)->update();

        return $effectiveWarpdriveProduction * $wrapper->get()->getRump()->getFlightECost();
    }

    private function doConstructionStuff(StationInterface $station, InformationWrapper $informationWrapper): bool
    {
        $progress =  $station->getConstructionProgress();
        if ($progress === null) {
            return false;
        }

        if ($progress->getRemainingTicks() === 0) {
            return false;
        }

        $isUnderConstruction = $station->getState() === SpacecraftStateEnum::UNDER_CONSTRUCTION;

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = $isUnderConstruction ? $station->getRump()->getNeededWorkbees() :
                (int)ceil($station->getRump()->getNeededWorkbees() / 2);

            $informationWrapper->addInformationf(
                'Nicht genügend Workbees (%d/%d) angedockt um %s weiterführen zu können',
                $this->stationUtility->getDockedWorkbeeCount($station),
                $neededWorkbees ?? 0,
                $isUnderConstruction ? 'den Bau' : 'die Demontage'
            );
            return true;
        }

        if ($isUnderConstruction) {
            // raise hull
            $increase = (int)ceil($station->getMaxHull() / (2 * $station->getRump()->getBuildtime()));
            $station->setHuell($station->getHull() + $increase);
        }

        if ($progress->getRemainingTicks() === 1) {

            $informationWrapper->addInformationf(
                '%s: %s bei %s fertiggestellt',
                $station->getRump()->getName(),
                $isUnderConstruction ? 'Bau' : 'Demontage',
                $station->getSectorString()
            );

            if ($isUnderConstruction) {
                $this->stationUtility->finishStation($progress);
            } else {
                $this->stationUtility->finishScrapping($progress, $informationWrapper);
            }
        } else {
            $this->stationUtility->reduceRemainingTicks($progress);
        }

        return true;
    }

    private function doRepairStation(StationWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        $station = $wrapper->get();

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = (int)ceil($station->getRump()->getNeededWorkbees() / 5);

            $informationWrapper->addInformationf(
                'Nicht genügend Workbees (%d/%d) angedockt um die Reparatur weiterführen zu können',
                $this->stationUtility->getDockedWorkbeeCount($station),
                $neededWorkbees
            );
            return;
        }

        $neededParts = $this->repairUtil->determineSpareParts($wrapper, true);

        // parts stored?
        if (!$this->repairUtil->enoughSparePartsOnEntity($neededParts, $station, $station)) {
            return;
        }

        //repair hull
        $station->setHuell($station->getHull() + $station->getRepairRate());
        if ($station->getHull() > $station->getMaxHull()) {
            $station->setHuell($station->getMaxHull());
        }

        //repair station systems
        $damagedSystems = $wrapper->getDamagedSystems();
        if ($damagedSystems !== []) {
            $firstSystem = $damagedSystems[0];
            $firstSystem->setStatus(100);

            if ($station->getCrewCount() > 0) {
                $firstSystem->setMode($this->spacecraftSystemManager->lookupSystem($firstSystem->getSystemType())->getDefaultMode());
            }

            // maximum of two systems get repaired
            if (count($damagedSystems) > 1) {
                $secondSystem = $damagedSystems[1];
                $secondSystem->setStatus(100);

                if ($station->getCrewCount() > 0) {
                    $secondSystem->setMode($this->spacecraftSystemManager->lookupSystem($secondSystem->getSystemType())->getDefaultMode());
                }
            }
        }

        // consume spare parts
        $this->repairUtil->consumeSpareParts($neededParts, $station);

        if (!$wrapper->canBeRepaired()) {
            $station->setHuell($station->getMaxHull());
            $station->setState(SpacecraftStateEnum::NONE);

            $shipOwnerMessage = sprintf(
                "Die Reparatur der %s wurde in Sektor %s fertiggestellt",
                $station->getName(),
                $station->getSectorString()
            );

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $station->getUser()->getId(),
                $shipOwnerMessage,
                PrivateMessageFolderTypeEnum::SPECIAL_STATION
            );
        }
        $this->spacecraftRepository->save($station);
    }

    private function checkForFinishedTakeover(SpacecraftInterface $spacecraft): void
    {
        $startTime = microtime(true);
        $takeover = $spacecraft->getTakeoverActive();
        if ($takeover === null) {
            return;
        }
        $this->potentialLog($spacecraft, "marker10.1", $startTime);

        $startTime = microtime(true);
        $isTakeoverReady = $this->shipTakeoverManager->isTakeoverReady($takeover);
        $this->potentialLog($spacecraft, "marker10.2", $startTime);

        if ($isTakeoverReady) {
            $startTime = microtime(true);
            $this->shipTakeoverManager->finishTakeover($takeover);
            $this->potentialLog($spacecraft, "marker10.3", $startTime);
        }
    }

    private function checkForFinishedAstroMapping(SpacecraftWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();

        $startTime = microtime(true);

        /** @var null|DatabaseEntryInterface $databaseEntry */
        /** @var string $message */
        [$message, $databaseEntry] = $this->getDatabaseEntryForShipLocation($ship);

        $astroLab = $wrapper->getAstroLaboratorySystemData();

        $this->potentialLog($ship, "marker11.1", $startTime);

        if (
            $ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING
            && $databaseEntry !== null
            && $astroLab !== null
            && $this->game->getCurrentRound()->getTurn() >= ($astroLab->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH)
        ) {

            $startTime = microtime(true);
            $this->astroEntryLib->finish($wrapper);
            $this->potentialLog($ship, "marker11.2", $startTime);

            $informationWrapper->addInformationf(
                'Die Kartographierung %s wurde vollendet',
                $message
            );

            $userId = $ship->getUser()->getId();
            $databaseEntryId = $databaseEntry->getId();

            $startTime = microtime(true);

            if (!$this->databaseUserRepository->exists($userId, $databaseEntryId)) {

                $this->potentialLog($ship, "marker11.3", $startTime);
                $startTime = microtime(true);

                $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($ship->getUser(), $databaseEntryId);

                if ($entry !== null) {
                    $informationWrapper->addInformationf(
                        'Neuer Datenbankeintrag: %s (+%d Punkte)',
                        $entry->getDescription(),
                        $entry->getCategory()->getPoints()
                    );
                }
            }

            $this->potentialLog($ship, "marker11.4", $startTime);
        }
    }

    /**
     * @return array{0: string|null, 1: DatabaseEntryInterface|null}
     */
    private function getDatabaseEntryForShipLocation(ShipInterface $ship): array
    {
        $system = $ship->getSystem();
        if ($system !== null) {
            return [
                'des Systems ' . $system->getName(),
                $system->getDatabaseEntry()
            ];
        }

        $mapRegion = $ship->getMapRegion();
        if ($mapRegion !== null) {
            return [
                'der Region ' . $mapRegion->getDescription(),
                $mapRegion->getDatabaseEntry()
            ];
        }

        return [null, null];
    }

    private function doTrackerDeviceStuff(SpacecraftWrapperInterface $wrapper): void
    {
        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();
        $tracker = $wrapper->getTrackerSystemData();

        if ($tracker === null || $tracker->targetId === null) {
            return;
        }

        $targetWrapper = $tracker->getTargetWrapper();
        if ($targetWrapper === null) {
            throw new RuntimeException('should not happen');
        }

        $target = $targetWrapper->get();
        $shipLocation = $ship->getLocation();
        $targetLocation = $target->getLocation();
        $remainingTicks = $tracker->getRemainingTicks();

        $reduceByTicks = max(1, (int)ceil((abs($shipLocation->getCx() - $targetLocation->getCx())
            +  abs($shipLocation->getCy() - $targetLocation->getCy())) / 50));

        //reduce remaining ticks
        if ($remainingTicks > $reduceByTicks) {
            $tracker->setRemainingTicks($remainingTicks - $reduceByTicks)->update();
        } else {
            $this->trackerDeviceManager->deactivateTrackerIfActive($wrapper, true);
        }
    }

    private function doBussardCollectorStuff(SpacecraftWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();
        $bussard = $wrapper->getBussardCollectorSystemData();
        $miningqueue = $ship->getMiningQueue();

        if ($bussard === null) {
            return;
        }

        if ($miningqueue == null) {
            return;
        } else {
            $locationmining = $miningqueue->getLocationMining();
            $actualAmount = $locationmining->getActualAmount();
            $freeStorage = $ship->getMaxStorage() - $ship->getStorageSum();
            $module = $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)->getModule();
            $gathercount = 0;

            if ($module !== null) {
                if ($module->getFactionId() == null) {
                    $gathercount =  (int) min(min(round(mt_rand(95, 105)), $actualAmount), $freeStorage);
                } else {
                    $gathercount = (int) min(min(round(mt_rand(190, 220)), $actualAmount), $freeStorage);
                }
            }

            $newAmount = $actualAmount - $gathercount;
            if ($gathercount > 0 && $locationmining->getDepletedAt() !== null) {
                $locationmining->setDepletedAt(null);
            }
            if ($newAmount == 0 && $actualAmount > 0) {
                $locationmining->setDepletedAt(time());
                $informationWrapper->addInformationf(
                    'Es sind keine %s bei den Koordinaten %s|%s vorhanden!',
                    $locationmining->getCommodity()->getName(),
                    (string)$locationmining->getLocation()->getCx(),
                    (string)$locationmining->getLocation()->getCy()
                );
            }
            $locationmining->setActualAmount($newAmount);

            $this->locationMiningRepository->save($locationmining);
            if ($gathercount + $ship->getStorageSum() >= $ship->getMaxStorage()) {
                $informationWrapper->addInformationf('Der Lagerraum des Schiffes wurde beim Sammeln von %s voll!', $locationmining->getCommodity()->getName());
            }

            if ($gathercount > 0) {
                $this->storageManager->upperStorage(
                    $ship,
                    $locationmining->getCommodity(),
                    $gathercount
                );
            }
        }
    }

    private function doAggregationSystemStuff(SpacecraftWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        if (!$wrapper instanceof StationWrapperInterface) {
            return;
        }

        $station = $wrapper->get();
        $aggsys = $wrapper->getAggregationSystemSystemData();

        if ($aggsys === null) {
            return;
        } else {
            $module = $station->getSpacecraftSystem(SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM)->getModule();
            $producedAmount = 0;
            $usedAmount = 0;
            $usedCommodity = null;
            $producedCommodity = null;


            if ($module !== null) {
                $commodity = $aggsys->getCommodityId();
                $commodities = CommodityTypeEnum::COMMODITY_CONVERSIONS;

                if ($commodity > 0) {
                    foreach ($commodities as $entry) {
                        if ($entry[0] === $commodity) {
                            $producedCommodityId = $entry[1];
                            $producedCommodity = $this->commodityRepository->find($producedCommodityId);
                            $usedCommodity = $this->commodityRepository->find($entry[0]);
                            $usedAmount = $entry[2];
                            $producedAmount = $entry[3];
                            break;
                        }
                    }

                    if ($module->getFactionId() == FactionEnum::FACTION_FERENGI) {
                        $producedAmount *= 2;
                        $usedAmount *= 2;
                    }
                    $storage = $this->storageRepository->findOneBy([
                        'commodity' => $usedCommodity,
                        'spacecraft' => $station
                    ]);
                    if (!$storage && $usedCommodity) {
                        $informationWrapper->addInformationf('Es ist kein %s vorhanden!', $usedCommodity->getName());
                    }

                    if ($storage && $producedCommodity && $usedCommodity) {
                        if ($storage->getAmount() >= $usedAmount) {
                            $this->storageManager->lowerStorage(
                                $station,
                                $usedCommodity,
                                $usedAmount
                            );
                            $this->storageManager->upperStorage(
                                $station,
                                $producedCommodity,
                                $producedAmount
                            );
                        } else {
                            $informationWrapper->addInformationf('Nicht genügend %s vorhanden!', $usedCommodity->getName());
                        }
                    }
                }
            }
        }
    }



    private function sendMessage(SpacecraftInterface $ship, InformationWrapper $informationWrapper): void
    {
        if ($informationWrapper->isEmpty()) {
            return;
        }

        $text = sprintf("Tickreport der %s\n%s", $ship->getName(), $informationWrapper->getInformationsAsString());

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $ship->getUser()->getId(),
            $text,
            $ship->getType()->getMessageFolderType(),
            $ship
        );
    }
}
