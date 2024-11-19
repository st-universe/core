<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Override;
use RuntimeException;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class FlightDirectionConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(private UpdateFlightDirectionInterface $updateFlightDirection) {}

    #[Override]
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        //leaving star system
        if ($flightRoute->getRouteMode() === RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT) {
            $oldWaypoint = $ship->getLocation();
            if (!$oldWaypoint instanceof StarSystemMapInterface) {
                throw new RuntimeException('this should not happen');
            }

            $this->updateFlightDirection->updateWhenSystemExit($ship, $oldWaypoint);
        }
    }
}
