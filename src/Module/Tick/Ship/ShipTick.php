<?php

namespace Stu\Module\Tick\Ship;

use RuntimeException;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipTick implements ShipTickInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipLeaverInterface $shipLeaver;

    private GameControllerInterface $game;

    private AstroEntryLibInterface $astroEntryLib;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private StationUtilityInterface $stationUtility;

    private RepairUtilInterface $repairUtil;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    /**
     * @var array<string>
     */
    private array $msg = [];

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipLeaverInterface $shipLeaver,
        GameControllerInterface $game,
        AstroEntryLibInterface $astroEntryLib,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        StationUtilityInterface $stationUtility,
        RepairUtilInterface $repairUtil,
        ShipTakeoverManagerInterface $shipTakeoverManager
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipLeaver = $shipLeaver;
        $this->game = $game;
        $this->astroEntryLib = $astroEntryLib;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->stationUtility = $stationUtility;
        $this->repairUtil = $repairUtil;
        $this->shipTakeoverManager = $shipTakeoverManager;
    }

    public function work(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        // do construction stuff
        if ($this->doConstructionStuff($ship)) {
            $this->shipRepository->save($ship);
            $this->sendMessages($ship);
            return;
        }

        // repair station
        if ($ship->isBase() && $ship->getState() === ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            $this->doRepairStation($wrapper);
        }

        // leave ship
        if ($ship->getCrewCount() > 0 && !$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
            $this->msg[] = _('Die Lebenserhaltung ist ausgefallen:');
            $this->msg[] = $this->shipLeaver->evacuate($wrapper);
            $this->sendMessages($ship);
            return;
        }

        $eps = $wrapper->getEpsSystemData();
        $warpdrive = $wrapper->getWarpDriveSystemData();
        $warpcore = $wrapper->getWarpCoreSystemData();
        if ($eps === null) {
            return;
        }

        // not enough crew
        $availableWarpDrive = null;
        if (!$ship->hasEnoughCrew()) {
            $this->msg[] = _('Zu wenig Crew an Bord, Schiff ist nicht voll funktionsfähig! Systeme werden deaktiviert!');

            //deactivate all systems except life support
            foreach ($this->shipSystemManager->getActiveSystems($ship) as $system) {
                if ($system->getSystemType() != ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT) {
                    $this->shipSystemManager->deactivate($wrapper, $system->getSystemType(), true);
                }
            }



            $availableEps = $eps->getEps();
        } else {
            if ($warpcore != null) {
                $availableEps = (int)($eps->getEps() +  ($ship->getReactorOutputCappedByReactorLoad() * ($warpcore->getWarpCoreSplit() / 100)));
            } else {
                $availableEps = $eps->getEps() + $ship->getReactorOutputCappedByReactorLoad();
            }
            if ($warpdrive !== null) {
                $availableWarpDrive = $warpdrive->getWarpDrive() + $wrapper->getEffectiveWarpDriveProduction();
            }
        }

        //try to save energy by reducing alert state
        if ($wrapper->getEpsUsage() > $availableEps) {
            $malus = $wrapper->getEpsUsage() - $availableEps;
            $alertUsage = $ship->getAlertState()->value - 1;

            if ($alertUsage > 0) {
                $preState = $ship->getAlertState();
                $reduce = (int)min($malus, $alertUsage);

                $ship->setAlertState(ShipAlertStateEnum::from($preState->value - $reduce));
                $this->msg[] = sprintf(
                    _('Wechsel von %s auf %s wegen Energiemangel'),
                    $preState->getDescription(),
                    $ship->getAlertState()->getDescription()
                );
            }
        }

        //try to save energy by deactivating systems from low to high priority
        if ($wrapper->getEpsUsage() > $availableEps) {
            $activeSystems = $this->shipSystemManager->getActiveSystems($ship, true);

            foreach ($activeSystems as $system) {
                $energyConsumption = $this->shipSystemManager->getEnergyConsumption($system->getSystemType());
                if ($energyConsumption < 1) {
                    continue;
                }

                //echo "- eps: ".$eps." - usage: ".$wrapper->getEpsUsage()."\n";
                if ($availableEps - $wrapper->getEpsUsage() - $energyConsumption < 0) {
                    //echo "-- hit system: ".$system->getDescription()."\n";

                    $this->shipSystemManager->deactivate($wrapper, $system->getSystemType(), true);

                    $wrapper->lowerEpsUsage($energyConsumption);
                    $this->msg[] = $this->getSystemDescription($system) . ' deaktiviert wegen Energiemangel';

                    if ($ship->getCrewCount() > 0 && $system->getSystemType() == ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT) {
                        $this->msg[] = _('Die Lebenserhaltung ist ausgefallen:');
                        $this->msg[] = $this->shipLeaver->evacuate($wrapper);
                        $this->sendMessages($ship);
                        return;
                    }
                }
                if ($wrapper->getEpsUsage() <= $availableEps) {
                    break;
                }
            }
        }

        $newEps = $availableEps - $wrapper->getEpsUsage();
        $batteryReload = $ship->isBase()
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
        $usedwarpdrive = 0;
        if ($warpdrive !== null && $availableWarpDrive !== null) {
            if ($availableWarpDrive > $warpdrive->getMaxWarpDrive()) {
                $availableWarpDrive = $warpdrive->getMaxWarpDrive();
                $usedwarpdrive = ($warpdrive->getMaxWarpDrive() - $warpdrive->getWarpDrive()) * $ship->getRump()->getFlightECost();
            } else {
                $usedwarpdrive = $wrapper->getEffectiveWarpDriveProduction() * $ship->getRump()->getFlightECost();
            }
            $warpdrive->setWarpDrive($availableWarpDrive)->update();
        }

        $usedEnergy = $wrapper->getEpsUsage() + $batteryReload + ($newEps - $eps->getEps()) + $usedwarpdrive;
        //echo "--- Generated Id ".$ship->getId()." - eps: ".$eps." - usage: ".$wrapper->getEpsUsage()." - old eps: ".$ship->getEps()." - wk: ".$wkuse."\n";
        $eps->setEps($newEps)
            ->setBattery($eps->getBattery() + $batteryReload)
            ->update();


        //core OR fusion
        $ship->setReactorLoad($ship->getReactorLoad() - $usedEnergy);

        $this->checkForFinishedTakeover($ship);
        $this->checkForFinishedAstroMapping($ship);

        //update tracker status
        $this->doTrackerDeviceStuff($wrapper);

        $this->shipRepository->save($ship);

        $this->sendMessages($ship);
    }

    private function doConstructionStuff(ShipInterface $ship): bool
    {
        $progress =  $this->stationUtility->getConstructionProgress($ship);

        if ($progress === null) {
            return false;
        }

        if ($progress->getRemainingTicks() === 0) {
            return false;
        }

        $isUnderConstruction = $ship->getState() === ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION;

        if (!$this->stationUtility->hasEnoughDockedWorkbees($ship, $ship->getRump())) {
            $neededWorkbees = $isUnderConstruction ? $ship->getRump()->getNeededWorkbees() :
                (int)ceil($ship->getRump()->getNeededWorkbees() / 2);

            $this->msg[] = sprintf(
                _('Nicht genügend Workbees (%d/%d) angedockt um %s weiterführen zu können'),
                $this->stationUtility->getDockedWorkbeeCount($ship),
                $neededWorkbees,
                $isUnderConstruction ? 'den Bau' : 'die Demontage'
            );
            return true;
        }

        if ($progress->getRemainingTicks() === 1) {
            if ($isUnderConstruction) {
                $this->stationUtility->finishStation($ship, $progress);
            } else {
                $this->stationUtility->finishScrapping($ship, $progress);
            }

            $this->msg[] = sprintf(
                _('%s: %s bei %s fertiggestellt'),
                $ship->getRump()->getName(),
                $isUnderConstruction ? 'Bau' : 'Demontage',
                $ship->getSectorString()
            );
        } else {
            $this->stationUtility->reduceRemainingTicks($progress);

            if ($isUnderConstruction) {
                // raise hull
                $increase = intdiv($ship->getMaxHull(), 2 * $ship->getRump()->getBuildtime());
                $ship->setHuell($ship->getHull() + $increase);
            }
        }

        return true;
    }

    private function doRepairStation(ShipWrapperInterface $wrapper): void
    {
        $station = $wrapper->get();

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = (int)ceil($station->getRump()->getNeededWorkbees() / 5);

            $this->msg[] = sprintf(
                _('Nicht genügend Workbees (%d/%d) angedockt um die Reparatur weiterführen zu können'),
                $this->stationUtility->getDockedWorkbeeCount($station),
                $neededWorkbees
            );
            return;
        }

        $neededParts = $this->repairUtil->determineSpareParts($wrapper);

        // parts stored?
        if (!$this->repairUtil->enoughSparePartsOnEntity($neededParts, $station, false, $station)) {
            return;
        }

        //repair hull
        $station->setHuell($station->getHull() + $station->getRepairRate());
        if ($station->getHull() > $station->getMaxHull()) {
            $station->setHuell($station->getMaxHull());
        }

        //repair station systems
        $damagedSystems = $wrapper->getDamagedSystems();
        if (!empty($damagedSystems)) {
            $firstSystem = $damagedSystems[0];
            $firstSystem->setStatus(100);

            if ($station->getCrewCount() > 0) {
                $firstSystem->setMode($this->shipSystemManager->lookupSystem($firstSystem->getSystemType())->getDefaultMode());
            }

            // maximum of two systems get repaired
            if (count($damagedSystems) > 1) {
                $secondSystem = $damagedSystems[1];
                $secondSystem->setStatus(100);

                if ($station->getCrewCount() > 0) {
                    $secondSystem->setMode($this->shipSystemManager->lookupSystem($secondSystem->getSystemType())->getDefaultMode());
                }
            }
        }

        // consume spare parts
        $this->repairUtil->consumeSpareParts($neededParts, $station, false);

        if (!$wrapper->canBeRepaired()) {
            $station->setHuell($station->getMaxHull());
            $station->setState(ShipStateEnum::SHIP_STATE_NONE);

            $shipOwnerMessage = sprintf(
                "Die Reparatur der %s wurde in Sektor %s fertiggestellt",
                $station->getName(),
                $station->getSectorString()
            );

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $station->getUser()->getId(),
                $shipOwnerMessage,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
            );
        }
        $this->shipRepository->save($station);
    }

    private function checkForFinishedTakeover(ShipInterface $ship): void
    {
        $takeover = $ship->getTakeoverActive();
        if ($takeover === null) {
            return;
        }

        if ($this->shipTakeoverManager->isTakeoverReady($takeover)) {
            $this->shipTakeoverManager->finishTakeover($takeover);
        }
    }

    private function checkForFinishedAstroMapping(ShipInterface $ship): void
    {
        [$message, $databaseEntry] = $this->getDatabaseEntryForShipLocation($ship);

        if (
            $ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING
            && $databaseEntry !== null
            && $this->game->getCurrentRound()->getTurn() >= ($ship->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH)
        ) {
            $this->astroEntryLib->finish($ship);

            $this->msg[] = sprintf(
                _('Die Kartographierung %s wurde vollendet'),
                $message
            );

            $userId = $ship->getUser()->getId();
            $databaseEntryId = $databaseEntry->getId();

            if (!$this->databaseUserRepository->exists($userId, $databaseEntryId)) {
                $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($ship->getUser(), $databaseEntryId);

                if ($entry !== null) {
                    $this->msg[] = sprintf(
                        _('Neuer Datenbankeintrag: %s (+%d Punkte)'),
                        $entry->getDescription(),
                        $entry->getCategory()->getPoints()
                    );
                }
            }
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

    private function doTrackerDeviceStuff(ShipWrapperInterface $wrapper): void
    {
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
        $remainingTicks = $tracker->getRemainingTicks();

        $reduceByTicks = max(1, (int)ceil((abs($ship->getCx() - $target->getCx()) +  abs($ship->getCy() - $target->getCy())) / 50));

        //reduce remaining ticks
        if ($remainingTicks > $reduceByTicks) {
            $tracker->setRemainingTicks($remainingTicks - $reduceByTicks)->update();
        } else {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true);

            if ($target->getUser() !== $ship->getUser()) {
                //send pm to target owner
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $target->getUser()->getId(),
                    sprintf(
                        'Die Crew der %s hat einen Transponder gefunden und deaktiviert. %s',
                        $target->getName(),
                        $this->getTrackerSource($ship->getUser())
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );

                //send pm to tracker owner
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    sprintf(
                        'Die %s hat die Verbindung zum Tracker verloren',
                        $ship->getName()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }
        }
    }

    private function getTrackerSource(UserInterface $user): string
    {
        switch (random_int(0, 2)) {
            case 0:
                return _('Der Ursprung kann nicht identifiziert werden');
            case 1:
                return sprintf(_('Der Ursprung lässt auf %s schließen'), $user->getName());
            case 2:
                return sprintf(_('Der Ursprung lässt darauf schließen, dass er %s-Herkunft ist'), $user->getFaction()->getName());
            default:
                return '';
        }
    }

    private function getSystemDescription(ShipSystemInterface $shipSystem): string
    {
        return ShipSystemTypeEnum::getDescription($shipSystem->getSystemType());
    }

    private function sendMessages(ShipInterface $ship): void
    {
        if ($this->msg === []) {
            return;
        }
        $text = "Tickreport der " . $ship->getName() . "\n";
        foreach ($this->msg as $msg) {
            $text .= $msg . "\n";
        }

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $ship->getId());

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $ship->getUser()->getId(),
            $text,
            $ship->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $this->msg = [];
    }
}
