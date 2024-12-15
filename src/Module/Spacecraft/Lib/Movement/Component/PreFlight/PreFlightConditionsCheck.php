<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Override;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PreFlightConditionsCheck implements PreFlightConditionsCheckInterface
{
    /**
     * @param array<string, PreFlightConditionInterface> $conditions
     */
    public function __construct(private LeaveFleetInterface $leaveFleet, private array $conditions) {}

    #[Override]
    public function checkPreconditions(
        SpacecraftWrapperInterface $leader,
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
            function (PreFlightConditionInterface $condition) use ($wrappers, $flightRoute, $conditionCheckResult): void {
                array_walk(
                    $wrappers,
                    fn(SpacecraftWrapperInterface $wrapper) => $condition->check(
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
