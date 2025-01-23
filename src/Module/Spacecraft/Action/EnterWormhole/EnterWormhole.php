<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\EnterWormhole;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EnterWormhole extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_ENTER_WORMHOLE';

    private const MODE_ALLOWED = 1;
    private const MODE_DENIED = 2;

    #[Override]
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

        $restrictions = $wormholeEntry->getRestrictions();

        $hasAllowedEntry = false;
        foreach ($restrictions as $restriction) {
            if ($restriction->getMode() === self::MODE_ALLOWED) {
                $hasAllowedEntry = true;
                if ($restriction->getUser() === $ship->getUser()) {
                    return false;
                }
                $game->addInformation(_("Du hast keine Berechtigung um in das Wurmloch einzufliegen"));
            }
            if ($restriction->getUser() === $ship->getUser() && $restriction->getMode() === self::MODE_DENIED) {
                $game->addInformation(_("Du hast keine Berechtigung um in das Wurmloch einzufliegen"));
                return true;
            }
        }

        if ($hasAllowedEntry) {
            return true;
        }

        return $ship->isStation();
    }

    #[Override]
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
