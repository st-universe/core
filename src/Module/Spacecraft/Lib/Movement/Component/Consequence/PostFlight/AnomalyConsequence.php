<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Component\Anomaly\AnomalyHandlingInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class AnomalyConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(private AnomalyHandlingInterface $anomalyHandling) {}

    #[\Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[\Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $this->anomalyHandling->handleIncomingSpacecraft($wrapper, $messages);
    }
}
