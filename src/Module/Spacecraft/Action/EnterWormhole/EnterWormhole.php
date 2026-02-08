<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\EnterWormhole;

use RuntimeException;
use Stu\Component\Ship\Wormhole\WormholeEntryPrivilegeUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Spacecraft\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipRequestInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EnterWormhole extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_ENTER_WORMHOLE';

    private WormholeEntryPrivilegeUtilityInterface $wormholeEntryPrivilegeUtility;

    public function __construct(
        MoveShipRequestInterface $moveShipRequest,
        SpacecraftLoaderInterface $spacecraftLoader,
        ShipMoverInterface $shipMover,
        FlightRouteFactoryInterface $flightRouteFactory,
        RandomSystemEntryInterface $randomSystemEntry,
        DistributedMessageSenderInterface $distributedMessageSender,
        WormholeEntryPrivilegeUtilityInterface $wormholeEntryPrivilegeUtility
    ) {
        parent::__construct(
            $moveShipRequest,
            $spacecraftLoader,
            $shipMover,
            $flightRouteFactory,
            $randomSystemEntry,
            $distributedMessageSender
        );
        $this->wormholeEntryPrivilegeUtility = $wormholeEntryPrivilegeUtility;
    }

    #[\Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();
        $map = $ship->getMap();

        if ($map === null) {
            return true;
        }

        $wormholeEntry = $map->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            return true;
        }

        if ($ship->isWarped()) {
            return true;
        }

        if (!$this->wormholeEntryPrivilegeUtility->checkPrivilegeFor($wormholeEntry, $ship)) {
            $game->getInfo()->addInformation(_("Du hast keine Berechtigung um in das Wurmloch einzufliegen"));
            return true;
        }

        return $ship->isStation();
    }

    #[\Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();
        $map = $ship->getMap();

        if ($map === null) {
            throw new RuntimeException('should not happen');
        }

        $wormholeEntry = $map->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            throw new RuntimeException('should not happen');
        }

        return $this->flightRouteFactory->getRouteForWormholeDestination($wormholeEntry, true);
    }
}
