<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Override;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PreFlightConditionsCheck implements PreFlightConditionsCheckInterface
{
    /**
     * @param array<string, PreFlightConditionInterface> $conditions
     */
    public function __construct(
        private ConditionCheckResultFactoryInterface $conditionCheckResultFactory,
        private array $conditions
    ) {}

    #[Override]
    public function checkPreconditions(
        FlightCompany $flightCompany,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): ConditionCheckResult {

        $conditionCheckResult = $this->conditionCheckResultFactory->create(
            $flightCompany
        );

        $wrappers = $flightCompany->getActiveMembers()->toArray();

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

        if (!$conditionCheckResult->isFlightPossible()) {
            $messages->addInformation('Der Weiterflug wurde aus folgenden Gründen abgebrochen:');
        }

        $messages->addMessageBy($conditionCheckResult->getInformations());

        return $conditionCheckResult;
    }
}
