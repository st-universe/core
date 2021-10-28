<?php

namespace Stu\Module\Tick\Ship;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
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
        StationUtilityInterface $stationUtility
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
    }

    public function work(ShipInterface $ship): void
    {
        // do construction stuff
        if ($this->doConstructionStuff($ship)) {
            $this->shipRepository->save($ship);
            $this->sendMessages($ship);
            return;
        }

        // ship offline
        if ($ship->getCrewCount() == 0 && $ship->getBuildplan()->getCrew() > 0) {
            return;
        }

        // leave ship
        if ($ship->getCrewCount() > 0 && !$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
            $this->msg[] = _('Die Lebenserhaltung ist ausgefallen:');
            $this->msg[] = $this->shipLeaver->evacuate($ship);
            $this->sendMessages($ship);
            return;
        }

        // not enough crew
        if (!$ship->hasEnoughCrew()) {
            $this->msg[] = _('Zu wenig Crew an Bord, Schiff ist nicht voll funktionsfähig! Systeme werden deaktiviert!');

            //deactivate all systems except life support
            foreach ($ship->getActiveSystems() as $system) {
                if ($system->getSystemType() != ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT) {
                    $this->shipSystemManager->deactivate($ship, $system->getSystemType(), true);
                }
            }

            $eps = $ship->getEps();
        } else {
            $eps = $ship->getEps() + $ship->getReactorCapacity();
        }

        //try to save energy by reducing alert state
        if ($ship->getEpsUsage() > $eps) {
            $malus = $ship->getEpsUsage() - $eps;
            $alertUsage = $ship->getAlertState() - 1;

            if ($alertUsage > 0) {
                $preState = $ship->getAlertState();
                $reduce = min($malus, $alertUsage);

                $dummyMsg = null;
                $ship->setAlertState($preState - $reduce, $dummyMsg);
                $this->msg[] = sprintf(
                    _('Wechsel von %s auf %s wegen Energiemangel'),
                    ShipAlertStateEnum::getDescription($preState),
                    ShipAlertStateEnum::getDescription($ship->getAlertState())
                );
            }
        }

        //try to save energy by deactivating systems from low to high priority
        if ($ship->getEpsUsage() > $eps) {
            $activeSystems = $ship->getActiveSystems(true);

            foreach ($activeSystems as $system) {

                $energyConsumption = $this->shipSystemManager->getEnergyConsumption($system->getSystemType());
                if ($energyConsumption < 1) {
                    continue;
                }

                //echo "- eps: ".$eps." - usage: ".$ship->getEpsUsage()."\n";
                if ($eps - $ship->getEpsUsage() - $energyConsumption < 0) {
                    //echo "-- hit system: ".$system->getDescription()."\n";

                    $this->shipSystemManager->deactivate($ship, $system->getSystemType(), true);

                    $ship->lowerEpsUsage($energyConsumption);
                    $this->msg[] = $this->getSystemDescription($system) . ' deaktiviert wegen Energiemangel';

                    if ($ship->getCrewCount() > 0 && $system->getSystemType() == ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT) {
                        $this->msg[] = _('Die Lebenserhaltung ist ausgefallen:');
                        $this->msg[] = $this->shipLeaver->evacuate($ship);
                        $this->sendMessages($ship);
                        return;
                    }
                }
                if ($ship->getEpsUsage() <= $eps) {
                    break;
                }
            }
        }
        $eps -= $ship->getEpsUsage();
        if ($eps > $ship->getMaxEps()) {
            $eps = $ship->getMaxEps();
        }
        $wkuse = $ship->getEpsUsage() + ($eps - $ship->getEps());
        //echo "--- Generated Id ".$ship->getId()." - eps: ".$eps." - usage: ".$ship->getEpsUsage()." - old eps: ".$ship->getEps()." - wk: ".$wkuse."\n";
        $ship->setEps($eps);
        $ship->setWarpcoreLoad($ship->getWarpcoreLoad() - $wkuse);

        $this->checkForFinishedAstroMapping($ship);

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

        if (!$this->stationUtility->hasEnoughDockedWorkbees($ship, $ship->getRump())) {
            $this->msg[] = sprintf(
                _('Nicht genügend Workbees (%d/%d) angedockt um den Bau weiterführen zu können'),
                $this->stationUtility->getDockedWorkbeeCount($ship),
                $ship->getRump()->getNeededWorkbees()
            );
            return true;
        }

        if ($progress->getRemainingTicks() === 1) {
            $this->stationUtility->finishStation($ship, $progress);

            $this->msg[] = sprintf(
                _('%s: Bau bei %s fertiggestellt'),
                $ship->getRump()->getName(),
                $ship->getSectorString()
            );
        } else {
            $this->stationUtility->reduceRemainingTicks($progress);

            // raise hull
            $increase = intdiv($ship->getMaxHuell(), 2 * $ship->getRump()->getBuildtime());
            $ship->setHuell($ship->getHuell() + $increase);
        }

        return true;
    }

    private function checkForFinishedAstroMapping(ShipInterface $ship): void
    {
        if (
            $ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING
            && $this->game->getCurrentRound()->getTurn() >= ($ship->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH)
        ) {
            $this->astroEntryLib->finish($ship);
            $this->msg[] = sprintf(
                _('Die Kartographierung des Systems %s wurde vollendet'),
                $ship->getSystem()->getName()
            );

            $databaseEntry = $ship->getSystem()->getDatabaseEntry();
            if ($databaseEntry !== null) {
                $userId = $ship->getUser()->getId();
                $databaseEntryId = $databaseEntry->getId();

                if ($databaseEntryId > 0 && $this->databaseUserRepository->exists($userId, $databaseEntryId) === false) {
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

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int) $ship->getUser()->getId(),
            $text,
            $ship->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $this->msg = [];
    }
}
