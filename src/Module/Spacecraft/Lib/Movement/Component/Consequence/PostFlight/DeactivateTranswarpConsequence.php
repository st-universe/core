<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class DeactivateTranswarpConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(private SpacecraftSystemManagerInterface $spacecraftSystemManager) {}

    #[\Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[\Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {
        if (
            $wrapper instanceof ShipWrapperInterface
            && $wrapper->get()->isTractored()
        ) {
            return;
        }

        if ($flightRoute->getRouteMode() === RouteModeEnum::TRANSWARP) {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRANSWARP_COIL, true);
        }
    }
}
