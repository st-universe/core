<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\LeaveWormhole;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class LeaveWormhole extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_LEAVE_WORMHOLE';

    private const int MODE_ALLOWED = 1;
    private const int MODE_DENIED = 2;

    #[Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();
        $starsystemMap = $ship->getStarsystemMap();

        if ($starsystemMap === null) {
            return true;
        }

        if (!$starsystemMap->getSystem()->isWormhole()) {
            return true;
        }

        $wormholeEntry = $starsystemMap->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            return true;
        }

        $restrictions = $wormholeEntry->getRestrictions();

        $hasAllowedEntry = false;
        foreach ($restrictions as $restriction) {
            if ($restriction->getMode() === self::MODE_ALLOWED) {
                $hasAllowedEntry = true;
                if ($restriction->getUser()->getId() === $ship->getUser()->getId()) {
                    return false;
                }
            }
            if ($restriction->getUser()->getId() === $ship->getUser()->getId() && $restriction->getMode() === self::MODE_DENIED) {
                $game->getInfo()->addInformation(_("Du hast keine Berechtigung um das Wurmloch zu verlassen"));
                return true;
            }
        }

        if ($hasAllowedEntry) {
            $game->getInfo()->addInformation(_("Du hast keine Berechtigung um das Wurmloch zu verlassen"));
            return true;
        }

        return false;
    }

    #[Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();
        $starsystemMap = $ship->getStarsystemMap();

        if ($starsystemMap === null) {
            throw new RuntimeException('should not happen');
        }

        $wormholeEntry = $starsystemMap->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            throw new RuntimeException('should not happen');
        }

        return $this->flightRouteFactory->getRouteForWormholeDestination($wormholeEntry, false);
    }
}
