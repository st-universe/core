<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PostFlightDirectionConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(private FlightSignatureCreatorInterface $flightSignatureCreator, private UpdateFlightDirectionInterface $updateFlightDirection) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($flightRoute->isTraversing()) {

            $oldWaypoint = $flightRoute->getCurrentWaypoint();
            $waypoint = $flightRoute->getNextWaypoint();

            $flightDirection = $this->updateFlightDirection->updateWhenTraversing(
                $oldWaypoint,
                $waypoint,
                $ship
            );

            //create flight signatures
            if (
                ! $wrapper instanceof ShipWrapperInterface
                || !$wrapper->get()->isTractored()
            ) {
                $this->flightSignatureCreator->createSignatures(
                    $ship,
                    $flightDirection,
                    $oldWaypoint,
                    $waypoint,
                );
            }
        }
    }
}
