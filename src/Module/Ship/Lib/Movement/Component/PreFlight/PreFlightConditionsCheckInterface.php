<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface PreFlightConditionsCheckInterface
{
    /** @param array<ShipWrapperInterface> $wrappers */
    public function checkPreconditions(
        array $wrappers,
        FlightRouteInterface $flightRoute,
        bool $isFixedFleetMode
    ): ConditionCheckResult;
}
