<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;

interface ConditionCheckResultFactoryInterface
{
    public function create(FlightCompany $flightCompany): ConditionCheckResult;
}
