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

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

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

        // the destination starsystem map field
        $starsystemMap = $wormholeEntry->getSystemMap();

        // @todo Beschädigung bei Systemeinflug
        $this->enterWormhole($ship, $starsystemMap);
        if ($ship->isTractoring()) {
            $this->enterWormholeTraktor($ship, $starsystemMap, $game);
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
                if (!$fleetShip->hasEnoughCrew()) {
                    $msg[] = sprintf(
                        _("Die %s hat die Flotte verlassen. Grund: Zu wenig Crew"),
                        $fleetShip->getName()
                    );
                    $this->shipWrapperFactory->wrapShip($fleetShip)->leaveFleet();
                    continue;
                }
                if ($fleetShip->getEps() === 0) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Energiemangel";
                    $this->shipWrapperFactory->wrapShip($fleetShip)->leaveFleet();
                    continue;
                }

                try {
                    $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                }

                $this->enterWormhole($fleetShip, $starsystemMap);
                if ($fleetShip->isTractoring()) {
                    $this->enterWormholeTraktor($fleetShip, $starsystemMap, $game);
                }

                $fleetShip->setEps($fleetShip->getEps() - 1);

                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte fliegt in das " . $wormhole->getName() . " ein");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $this->shipWrapperFactory->wrapShip($ship)->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Schiff fliegt in das " . $wormhole->getName() . " ein");
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

    private function enterWormholeTraktor(ShipInterface $ship, StarSystemMapInterface $starsystemMap, GameControllerInterface $game): void
    {
        $tractoredShip = $ship->getTractoredShip();

        if (
            $tractoredShip->getFleetId()
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $name = $tractoredShip->getName();
            $this->shipWrapperFactory->wrapShip($ship)->deactivateTractorBeam(); //active deactivation

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Wurmlocheinflug deaktiviert'),
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
            $this->shipWrapperFactory->wrapShip($ship)->deactivateTractorBeam(); //active deactivation
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Wurmlocheinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->enterWormhole(
            $tractoredShip,
            $starsystemMap
        );
        // @todo Beschädigung bei Systemeinflug
        $ship->setEps($ship->getEps() - 1);
        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit in das Wurmloch gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($ship, $tractoredShip, $msg);
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
