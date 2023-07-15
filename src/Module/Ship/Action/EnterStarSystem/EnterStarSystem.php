<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;

final class EnterStarSystem extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

    protected function isSanityCheckFaulty(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();
        if ($system === null) {
            return true;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return true;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return true;
        }

        if ($ship->getHoldingWeb() !== null && $ship->getHoldingWeb()->isFinished()) {
            $game->addInformation(_('Das Schiff ist in einem Energienetz gefangen'));
            return true;
        }

        return false;
    }

    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();
        if ($system === null) {
            throw new RuntimeException('should not happen');
        }

        [$posx, $posy] = $this->getDestinationCoordinates($ship, $system);

        // the destination starsystem map field
        $starsystemMap = $this->starSystemMapRepository->getByCoordinates($system->getId(), $posx, $posy);

        if ($starsystemMap === null) {
            throw new RuntimeException('starsystem map is missing');
        }

        return $this->flightRouteFactory->getRouteForMapDestination($starsystemMap);
    }

    /**
     * @return array{0: int,1: int}
     */
    private function getDestinationCoordinates(ShipInterface $ship, StarSystemInterface $system): array
    {
        $flightDirection = $ship->getFlightDirection();
        if ($flightDirection === 0) {
            $flightDirection = rand(1, 4);
        }

        switch ($flightDirection) {
            case ShipEnum::DIRECTION_BOTTOM:
                $posx = rand(1, $system->getMaxX());
                $posy = 1;
                break;
            case ShipEnum::DIRECTION_TOP:
                $posx = rand(1, $system->getMaxX());
                $posy = $system->getMaxY();
                break;
            case ShipEnum::DIRECTION_RIGHT:
                $posx = 1;
                $posy = rand(1, $system->getMaxY());
                break;
            case ShipEnum::DIRECTION_LEFT:
                $posx = $system->getMaxX();
                $posy = rand(1, $system->getMaxY());
                break;
            default:
                throw new RuntimeException('unsupported flight direction');
        }

        return [$posx, $posy];
    }
}
