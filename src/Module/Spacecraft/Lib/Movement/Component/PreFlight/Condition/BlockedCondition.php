<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

class BlockedCondition implements PreFlightConditionInterface
{
    #[Override]
    public function check(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        $ship = $wrapper->get();

        if ($ship instanceof Ship && $ship->isTractored()) {
            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(_('Die %s wird von einem Traktorstrahl gehalten'), $ship->getName())
            );

            return;
        }

        $holdingWeb = $ship->getHoldingWeb();
        if ($holdingWeb !== null && $holdingWeb->isFinished()) {
            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(_('Die %s wird von einem Energienetz gehalten'), $ship->getName())
            );
        }
    }
}
