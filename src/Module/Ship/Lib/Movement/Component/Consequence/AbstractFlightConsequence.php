<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence;

use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

abstract class AbstractFlightConsequence implements FlightConsequenceInterface
{
    public function trigger(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        FightMessageCollectionInterface $messages
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
        FightMessageCollectionInterface $messages
    ): void;
}
