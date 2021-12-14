<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class LeaveStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private MapRepositoryInterface $mapRepository;

    private ActivatorDeactivatorHelperInterface $helper;

    private AstroEntryLibInterface $astroEntryLib;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        MapRepositoryInterface $mapRepository,
        ActivatorDeactivatorHelperInterface $helper,
        AstroEntryLibInterface $astroEntryLib,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->mapRepository = $mapRepository;
        $this->helper = $helper;
        $this->astroEntryLib = $astroEntryLib;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        if ($userId === 126) {
            $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        }

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getSystem() === null) {
            return;
        }

        if ($ship->isFleetLeader() && $ship->getFleet()->getDefendedColony() !== null) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Verteidigung nicht möglich'));
            return;
        }

        if ($ship->isFleetLeader() && $ship->getFleet()->getBlockedColony() !== null) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Blockierung nicht möglich'));
            return;
        }

        if (!$this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game)) {
            return;
        }

        //reload ship because it got saved in helper class
        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        //the destination map field
        $outerMap = $this->mapRepository->getByCoordinates($ship->getSystem()->getCx(), $ship->getSystem()->getCy());

        $this->leaveStarSystem($ship, $outerMap, $game);
        if ($ship->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($ship, $outerMap, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();

            /** @var ShipInterface[] $result */
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship !== $fleetShip;
                }
            );
            foreach ($result as $fleetShip) {
                if (!$this->helper->activate($fleetShip->getId(), ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game)) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: Warpantrieb kann nicht aktiviert werden";
                    $fleetShip->leaveFleet();
                    $this->shipRepository->save($fleetShip);
                } else {
                    //reload ship because it got saved in helper class
                    $reloadedShip = $this->shipLoader->getByIdAndUser(
                        $fleetShip->getId(),
                        $userId
                    );

                    $this->leaveStarSystem($reloadedShip, $outerMap, $game);
                    if ($reloadedShip->isTraktorbeamActive()) {
                        $this->leaveStarSystemTraktor($reloadedShip, $outerMap, $game);
                    }
                    $this->shipRepository->save($reloadedShip);
                }
            }
            $game->addInformation("Die Flotte hat das Sternsystem verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Sternsystem wurde verlassen");
        }

        $this->shipRepository->save($ship);
    }

    private function leaveStarSystemTraktor(ShipInterface $ship, MapInterface $map, GameControllerInterface $game): void
    {
        if (
            $ship->getTraktorMode() == 1 && $ship->getTraktorShip()->getFleetId()
            && $ship->getTraktorShip()->getFleet()->getShipCount() > 1
        ) {
            $name = $ship->getTraktorShip()->getName();
            $ship->deactivateTraktorBeam();

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Verlassen des Systems deaktiviert'),
                $name
            ));
            return;
        }
        if ($ship->getEps() < 1) {
            $name = $ship->getTraktorShip()->getName();
            $ship->deactivateTraktorBeam();
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->leaveStarSystem($ship->getTraktorShip(), $map, $game);
        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship->getTraktorShip());
        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    private function leaveStarSystem(ShipInterface $ship, MapInterface $map, GameControllerInterface $game): void
    {
        $ship->setFlightDirection($this->getNewDirection($ship));

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
        $this->loggerUtil->log(sprintf('newDirection: %d', $ship->getFlightDirection()));
=======
        $this->loggerUtil->log('newDirection: %d', $ship->getFlightDirection());
>>>>>>> leaving star system changes direction based on location
=======
        $this->loggerUtil->log(sprintf('newDirection: %d', $ship->getFlightDirection()));
>>>>>>> bugfix
=======
        $this->loggerUtil->log('newDirection: %d', $ship->getFlightDirection());
>>>>>>> leaving star system changes direction based on location
=======
        $this->loggerUtil->log(sprintf('newDirection: %d', $ship->getFlightDirection()));
>>>>>>> bugfix

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
            $game->addInformation(sprintf(_('Die %s hat die Kartographierungs-Finalisierung abgebrochen'), $ship->getName()));
        }

        $ship->setMap($map);
        $ship->setStarsystemMap(null);
    }

    private function getNewDirection(ShipInterface $ship): int
    {
        $starsystemMap = $ship->getStarsystemMap();
        $system = $starsystemMap->getSystem();

        $shipX = $starsystemMap->getSx();
        $shipY = $starsystemMap->getSy();

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
        $this->loggerUtil->log(sprintf('ship (x|y) %d|%d, systemMaxX %d', $shipX, $shipY, $system->getMaxX()));

        $rad12or34 = atan($shipY / $shipX);
        $rad14or23 = atan(($system->getMaxX() - $shipX) / $shipY);

        $this->loggerUtil->log(sprintf('rad12or34: %F, rad14or23: %F', $rad12or34, $rad14or23));
=======
        $this->loggerUtil->log('ship (x|y) %d|%d, systemMaxX %d', $shipX, $shipY, $system->getMaxX());
=======
        $this->loggerUtil->log(sprintf('ship (x|y) %d|%d, systemMaxX %d', $shipX, $shipY, $system->getMaxX()));
>>>>>>> bugfix

        $rad12or34 = atan($shipY / $shipX);
        $rad14or23 = atan(($system->getMaxX() - $shipX) / $shipY);

<<<<<<< HEAD
        $this->loggerUtil->log('rad12or34: %F, rad14or23: %F', $rad12or34, $rad14or23);
>>>>>>> leaving star system changes direction based on location
=======
        $this->loggerUtil->log(sprintf('rad12or34: %F, rad14or23: %F', $rad12or34, $rad14or23));
>>>>>>> bugfix
=======
        $this->loggerUtil->log('ship (x|y) %d|%d, systemMaxX %d', $shipX, $shipY, $system->getMaxX());
=======
        $this->loggerUtil->log(sprintf('ship (x|y) %d|%d, systemMaxX %d', $shipX, $shipY, $system->getMaxX()));
>>>>>>> bugfix

        $rad12or34 = atan($shipY / $shipX);
        $rad14or23 = atan($system->getMaxX() - $shipX / $shipY);

<<<<<<< HEAD
        $this->loggerUtil->log('rad12or34: %F, rad14or23: %F', $rad12or34, $rad14or23);
>>>>>>> leaving star system changes direction based on location
=======
        $this->loggerUtil->log(sprintf('rad12or34: %F, rad14or23: %F', $rad12or34, $rad14or23));
>>>>>>> bugfix

        if ($rad12or34 < M_PI_4) {
            if ($rad14or23 < M_PI_4) {
                return ShipEnum::DIRECTION_LEFT;
            } else {
                return ShipEnum::DIRECTION_BOTTOM;
            }
        } else {
            if ($rad14or23 < M_PI_4) {
                return ShipEnum::DIRECTION_TOP;
            } else {
                return ShipEnum::DIRECTION_RIGHT;
            }
        }
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
