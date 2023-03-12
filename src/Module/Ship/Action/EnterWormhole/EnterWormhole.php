<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterWormhole;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class EnterWormhole implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ENTER_WORMHOLE';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private WormholeEntryRepositoryInterface $wormholeEntryRepository;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        WormholeEntryRepositoryInterface $wormholeEntryRepository,
        AlertRedHelperInterface $alertRedHelper,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->wormholeEntryRepository = $wormholeEntryRepository;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $map = $ship->getMap();

        if ($map === null) {
            return;
        }

        $wormholeEntry = $map->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            return;
        }

        if ($ship->getWarpState()) {
            return;
        }

        if ($ship->isBase()) {
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

        // the destination starsystem map field
        $starsystemMap = $wormholeEntry->getSystemMap();

        // @todo Beschädigung bei Systemeinflug
        $this->enterWormhole($ship, $starsystemMap);
        if ($ship->isTractoring()) {
            $this->enterWormholeTraktor($wrapper, $starsystemMap, $game);
        }

        $wormhole = $wormholeEntry->getSystem();

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
                $wrapper = $this->shipWrapperFactory->wrapShip($fleetShip);

                if (!$fleetShip->hasEnoughCrew()) {
                    $msg[] = sprintf(
                        _("Die %s hat die Flotte verlassen. Grund: Zu wenig Crew"),
                        $fleetShip->getName()
                    );
                    $this->shipWrapperFactory->wrapShip($fleetShip)->leaveFleet();
                    continue;
                }

                $epsSystem = $wrapper->getEpsSystemData();

                if ($epsSystem->getEps() === 0) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Energiemangel";
                    $this->shipWrapperFactory->wrapShip($fleetShip)->leaveFleet();
                    continue;
                }

                try {
                    $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                }

                $this->enterWormhole($fleetShip, $starsystemMap);
                if ($fleetShip->isTractoring()) {
                    $this->enterWormholeTraktor($wrapper, $starsystemMap, $game);
                }

                $epsSystem->setEps($epsSystem->getEps() - 1)->update();

                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte fliegt in das " . $wormhole->getName() . " ein");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleet() !== null) {
                $this->shipWrapperFactory->wrapShip($ship)->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Schiff fliegt in das " . $wormhole->getName() . " ein");
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

    private function enterWormholeTraktor(ShipWrapperInterface $wrapper, StarSystemMapInterface $starsystemMap, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();

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
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Wurmlocheinflug deaktiviert'),
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
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Wurmlocheinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->enterWormhole(
            $tractoredShip,
            $starsystemMap
        );
        // @todo Beschädigung bei Systemeinflug
        $epsSystem->setEps($epsSystem->getEps() - 1)->update();
        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit in das Wurmloch gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($wrapper, $tractoredShip, $msg);
        $game->addInformationMergeDown($msg);


        $this->shipRepository->save($tractoredShip);
        $this->shipRepository->save($ship);
    }

    private function enterWormhole(ShipInterface $ship, StarSystemMapInterface $starsystemMap): void
    {
        $ship->updateLocation(null, $starsystemMap);
        $ship->setDockedTo(null);
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        $ship->setCx(0);
        $ship->setCy(0);

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
