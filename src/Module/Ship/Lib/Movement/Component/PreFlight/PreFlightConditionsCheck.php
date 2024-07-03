<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PreFlightConditionsCheck implements PreFlightConditionsCheckInterface
{
    private LeaveFleetInterface $leaveFleet;

    /** @var array<string, PreFlightConditionInterface>  */
    private array $conditions;

    /**
     * @param array<string, PreFlightConditionInterface> $conditions
     */
    public function __construct(
        LeaveFleetInterface $leaveFleet,
        array $conditions
    ) {
        $this->leaveFleet = $leaveFleet;
        $this->conditions = $conditions;
    }

    public function checkPreconditions(
        ShipWrapperInterface $leader,
        array $wrappers,
        FlightRouteInterface $flightRoute,
        bool $isFixedFleetMode
    ): ConditionCheckResult {
        $conditionCheckResult = new ConditionCheckResult(
            $this->leaveFleet,
            $leader,
            $isFixedFleetMode
        );

        array_walk(
            $this->conditions,
            function (PreFlightConditionInterface $condition) use ($wrappers, $flightRoute, $conditionCheckResult) {
                array_walk(
                    $wrappers,
                    fn (ShipWrapperInterface $wrapper) => $condition->check(
                        $wrapper,
                        $flightRoute,
                        $conditionCheckResult
                    )
                );
            }
        );

        return $conditionCheckResult;
    }
}
