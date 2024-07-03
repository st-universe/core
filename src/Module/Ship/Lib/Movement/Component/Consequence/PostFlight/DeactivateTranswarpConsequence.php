<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class DeactivateTranswarpConsequence extends AbstractFlightConsequence
{
    public function __construct(private ShipSystemManagerInterface $shipSystemManager)
    {
    }

    #[Override]
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {
        if ($wrapper->get()->isTractored()) {
            return;
        }

        if ($flightRoute->getRouteMode() === RouteModeEnum::ROUTE_MODE_TRANSWARP) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, true);
        }
    }
}
