<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ActivatorDeactivatorHelperInterface $helper;

    private AstroEntryLibInterface $astroEntryLib;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ActivatorDeactivatorHelperInterface $helper,
        AstroEntryLibInterface $astroEntryLib,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->helper = $helper;
        $this->astroEntryLib = $astroEntryLib;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            return;
        }

        //the destination map field
        $outerMap = $starsystemMap->getSystem()->getMapField();
        if ($outerMap === null) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return;
        }

        if ($ship->getHoldingWeb() !== null && $ship->getHoldingWeb()->isFinished()) {
            $game->addInformation(_('Das Schiff ist in einem Energienetz gefangen'));
            return;
        }

        if (
            $ship->isFleetLeader()
            && $ship->getFleet() !== null
            && $ship->getFleet()->getDefendedColony() !== null
        ) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Verteidigung nicht möglich'));
            return;
        }

        if (
            $ship->isFleetLeader()
            && $ship->getFleet() !== null
            && $ship->getFleet()->getBlockedColony() !== null
        ) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Blockierung nicht möglich'));
            return;
        }

        if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game)) {
            return;
        }

        $this->leaveStarSystem($wrapper, $starsystemMap, $outerMap, $game);

        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {
            $this->leaveStarSystemTraktor($tractoredShipWrapper, $starsystemMap, $wrapper, $outerMap, $game);
        }

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($ship->isFleetLeader() && $fleetWrapper !== null) {
            $msg = [];

            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $fleetShip = $wrapper->get();

                if ($fleetShip === $ship) {
                    continue;
                }

                if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game)) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Warpantrieb kann nicht aktiviert werden";
                    $wrapper->leaveFleet();
                    $this->shipRepository->save($fleetShip);
                } else {
                    $this->leaveStarSystem($wrapper, $starsystemMap, $outerMap, $game);

                    $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
                    if ($tractoredShipWrapper !== null) {
                        $this->leaveStarSystemTraktor($tractoredShipWrapper, $starsystemMap, $wrapper, $outerMap, $game);
                    }
                    $this->shipRepository->save($fleetShip);
                }
            }
            $game->addInformation("Die Flotte hat das Sternsystem verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId() !== null) {
                $wrapper->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Sternsystem wurde verlassen");
        }

        $this->shipRepository->save($ship);
    }

    private function leaveStarSystemTraktor(
        ShipWrapperInterface $tractoredShipWrapper,
        StarSystemMapInterface $currentField,
        ShipWrapperInterface $wrapper,
        MapInterface $map,
        GameControllerInterface $game
    ): void {
        $ship = $wrapper->get();

        $tractoredShip = $tractoredShipWrapper->get();

        if (
            $tractoredShip->getFleet() !== null
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $name = $tractoredShip->getName();
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Verlassen des Systems deaktiviert'),
                $name
            ));
            return;
        }

        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip);
        if ($abortionMsg !== null) {
            $game->addInformation($abortionMsg);
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $name = $tractoredShip->getName();
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $game->addInformationMergeDown($this->cancelColonyBlockOrDefend->work($ship, true));
        $this->leaveStarSystem($tractoredShipWrapper, $currentField, $map, $game);
        $epsSystem->lowerEps(1)->update();

        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit aus dem System gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($wrapper, $tractoredShip, $msg);
        $game->addInformationMergeDown($msg);

        $this->shipRepository->save($tractoredShip);
        $this->shipRepository->save($ship);
    }

    private function leaveStarSystem(
        ShipWrapperInterface $wrapper,
        StarSystemMapInterface $currentField,
        MapInterface $map,
        GameControllerInterface $game
    ): void {
        $ship = $wrapper->get();
        $ship->setFlightDirection($this->getNewDirection($ship, $currentField));

        $this->loggerUtil->log(sprintf('newDirection: %d', $ship->getFlightDirection()));

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
            $game->addInformation(sprintf(_('Die %s hat die Kartographierungs-Finalisierung abgebrochen'), $ship->getName()));
        }

        $ship->setDockedTo(null);
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        $ship->updateLocation($map, null);
    }

    private function getNewDirection(ShipInterface $ship, StarSystemMapInterface $starsystemMap): int
    {
        $system = $starsystemMap->getSystem();

        $shipX = $starsystemMap->getSx();
        $shipY = $starsystemMap->getSy();

        $this->loggerUtil->log(sprintf('ship (x|y) %d|%d, systemMaxX %d', $shipX, $shipY, $system->getMaxX()));

        $rad12or34 = atan($shipY / $shipX);
        $rad14or23 = atan(($system->getMaxX() - $shipX) / $shipY);

        $this->loggerUtil->log(sprintf('rad12or34: %F, rad14or23: %F', $rad12or34, $rad14or23));

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
