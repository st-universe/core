<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;

interface PreFlightConditionsCheckInterface
{
    public function checkPreconditions(
        FlightCompany $flightCompany,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): ConditionCheckResult;
}
