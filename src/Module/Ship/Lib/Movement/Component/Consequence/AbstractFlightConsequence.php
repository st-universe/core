<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence;

use Override;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

abstract class AbstractFlightConsequence implements FlightConsequenceInterface
{
    #[Override]
    public function trigger(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {
        if ($wrapper->get()->isDestroyed()) {
            return;
        }

        $this->triggerSpecific(
            $wrapper,
            $flightRoute,
            $messages
        );
    }

    protected abstract function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void;
}
