<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;

class ConditionCheckResultFactory implements ConditionCheckResultFactoryInterface
{
    public function __construct(
        private LeaveFleetInterface $leaveFleet
    ) {}

    public function create(FlightCompany $flightCompany): ConditionCheckResult
    {
        return new ConditionCheckResult(
            $this->leaveFleet,
            $flightCompany
        );
    }
}
