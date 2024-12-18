<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence;

use Override;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

abstract class AbstractFlightConsequence implements FlightConsequenceInterface
{
    #[Override]
    public function trigger(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        if ($wrapper->get()->isDestroyed()) {
            return;
        }

        if (
            $this->skipWhenTractored()
            && $wrapper instanceof ShipWrapperInterface
            && $wrapper->get()->isTractored()
        ) {
            return;
        }

        $this->triggerSpecific(
            $wrapper,
            $flightRoute,
            $messages
        );
    }

    protected abstract function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void;

    protected abstract function skipWhenTractored(): bool;
}
