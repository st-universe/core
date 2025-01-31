<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface PreFlightConditionsCheckInterface
{
    /** @param array<SpacecraftWrapperInterface> $wrappers */
    public function checkPreconditions(
        SpacecraftWrapperInterface $leader,
        array $wrappers,
        FlightRouteInterface $flightRoute,
        bool $isFixedFleetMode
    ): ConditionCheckResult;
}
