<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Override;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PreFlightConditionsCheck implements PreFlightConditionsCheckInterface
{
    /**
     * @param array<string, PreFlightConditionInterface> $conditions
     */
    public function __construct(private LeaveFleetInterface $leaveFleet, private array $conditions)
    {
    }

    #[Override]
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
