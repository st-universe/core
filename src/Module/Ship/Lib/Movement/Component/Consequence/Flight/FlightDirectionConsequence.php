<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use RuntimeException;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\Movement\Route\UpdateFlightDirectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class FlightDirectionConsequence extends AbstractFlightConsequence
{
    private UpdateFlightDirectionInterface $updateFlightDirection;

    public function __construct(
        UpdateFlightDirectionInterface $updateFlightDirection
    ) {
        $this->updateFlightDirection = $updateFlightDirection;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        FightMessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        //leaving star system
        if ($flightRoute->getRouteMode() === RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT) {
            $oldWaypoint = $ship->getCurrentMapField();
            if (!$oldWaypoint instanceof StarSystemMapInterface) {
                throw new RuntimeException('this should not happen');
            }

            $this->updateFlightDirection->updateWhenSystemExit($ship, $oldWaypoint);
        }
    }
}
