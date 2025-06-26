<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Override;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StarSystemMap;

class FlightDirectionConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(private UpdateFlightDirectionInterface $updateFlightDirection) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        //leaving star system
        if ($flightRoute->getRouteMode() === RouteModeEnum::SYSTEM_EXIT) {
            $oldWaypoint = $wrapper->get()->getLocation();
            if (!$oldWaypoint instanceof StarSystemMap) {
                throw new RuntimeException('this should not happen');
            }

            $this->updateFlightDirection->updateWhenSystemExit($wrapper, $oldWaypoint);
        }
    }
}
