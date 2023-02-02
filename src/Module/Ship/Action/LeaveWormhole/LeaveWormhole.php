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
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
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

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        WormholeEntryRepositoryInterface $wormholeEntryRepository,
        AlertRedHelperInterface $alertRedHelper,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->wormholeEntryRepository = $wormholeEntryRepository;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

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

        $this->leaveWormhole($wrapper, $outerMap);
        if ($ship->isTractoring()) {
            $this->leaveWormholeTraktor($wrapper, $outerMap, $game);
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
                $fleetShipWrapper = $this->shipWrapperFactory->wrapShip($fleetShip);
                $this->leaveWormhole($fleetShipWrapper, $outerMap);
                if ($fleetShip->isTractoring()) {
                    $this->leaveWormholeTraktor($fleetShipWrapper, $outerMap, $game);
                }
                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte hat das Wurmloch verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleet() !== null) {
                $wrapper->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Wurmloch wurde verlassen");
        }

        $wormholeEntry->setLastUsed(time());
        $this->wormholeEntryRepository->save($wormholeEntry);

        // alert red check
        $this->alertRedHelper->doItAll($ship, $game);

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->shipRepository->save($ship);
    }

    private function leaveWormholeTraktor(ShipWrapperInterface $wrapper, MapInterface $map, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();
        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();

        /**
         * @var ShipInterface
         */
        $tractoredShip = $ship->getTractoredShip();

        if (
            $tractoredShip->getFleet() !== null
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $name = $tractoredShip->getName();
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Verlassen des Wurmlochs deaktiviert'),
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

        if ($epsSystem->getEps() < 1) {
            $name = $tractoredShip->getName();
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Verlassen des Wurmlochs aufgrund Energiemangels deaktiviert");
            return;
        }
        $game->addInformationMergeDown($this->cancelColonyBlockOrDefend->work($ship, true));
        $this->leaveWormhole($tractoredShipWrapper, $map);
        $epsSystem->setEps($epsSystem->getEps() - 1)->update();

        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit aus dem Wurmloch gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($wrapper, $tractoredShip, $msg);
        $game->addInformationMergeDown($msg);

        $this->shipRepository->save($tractoredShip);
        $this->shipRepository->save($ship);
    }

    private function leaveWormhole(ShipWrapperInterface $wrapper, MapInterface $map): void
    {
        $ship = $wrapper->get();
        $this->loggerUtil->log(sprintf('newDirection: %d', $ship->getFlightDirection()));

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true);
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
