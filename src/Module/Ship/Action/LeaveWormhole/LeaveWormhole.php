<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveWormhole;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class LeaveWormhole implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_WORMHOLE';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private WormholeEntryRepositoryInterface $wormholeEntryRepository;

    private AlertRedHelperInterface $alertRedHelper;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        WormholeEntryRepositoryInterface $wormholeEntryRepository,
        AlertRedHelperInterface $alertRedHelper,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->wormholeEntryRepository = $wormholeEntryRepository;
        $this->alertRedHelper = $alertRedHelper;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getSystem() === null) {
            return;
        }

        if (!$ship->getSystem()->isWormhole()) {
            return;
        }

        $wormholeEntry = $ship->getStarsystemMap()->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return;
        }

        //reload ship because it got saved in helper class
        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        //the destination map field
        $outerMap = $wormholeEntry->getMap();

        $this->leaveWormhole($ship, $outerMap);
        if ($ship->isTractoring()) {
            $this->leaveWormholeTraktor($ship, $outerMap, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = [];

            /** @var ShipInterface[] $result */
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship !== $fleetShip;
                }
            );
            foreach ($result as $fleetShip) {
                $this->leaveWormhole($fleetShip, $outerMap);
                if ($fleetShip->isTractoring()) {
                    $this->leaveWormholeTraktor($fleetShip, $outerMap, $game);
                }
                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte hat das Wurmloch verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Wurmloch wurde verlassen");
        }

        $wormholeEntry->setLastUsed(time());
        $this->wormholeEntryRepository->save($wormholeEntry);

        // alert red check
        $this->alertRedHelper->doItAll($ship, $game);

        if ($ship->getIsDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->shipRepository->save($ship);
    }

    private function leaveWormholeTraktor(ShipInterface $ship, MapInterface $map, GameControllerInterface $game): void
    {
        $tractoredShip = $ship->getTractoredShip();

        if (
            $tractoredShip->getFleetId()
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $name = $tractoredShip->getName();
            $ship->deactivateTractorBeam(); //active deactivation

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Verlassen des Wurmlochs deaktiviert'),
                $name
            ));
            return;
        }

        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($ship, $tractoredShip);
        if ($abortionMsg !== null) {
            $game->addInformation($abortionMsg);
            return;
        }

        if ($ship->getEps() < 1) {
            $name = $tractoredShip->getName();
            $ship->deactivateTractorBeam(); //active deactivation
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Verlassen des Wurmlochs aufgrund Energiemangels deaktiviert");
            return;
        }
        $game->addInformationMergeDown($this->cancelColonyBlockOrDefend->work($ship, true));
        $this->leaveWormhole($tractoredShip, $map);
        $ship->setEps($ship->getEps() - 1);

        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit aus dem Wurmloch gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($ship, $tractoredShip, $msg);
        $game->addInformationMergeDown($msg);

        $this->shipRepository->save($tractoredShip);
        $this->shipRepository->save($ship);
    }

    private function leaveWormhole(ShipInterface $ship, MapInterface $map): void
    {
        $this->loggerUtil->log(sprintf('newDirection: %d', $ship->getFlightDirection()));

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true);
        }

        $ship->setDockedTo(null);
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        $ship->updateLocation($map, null);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
