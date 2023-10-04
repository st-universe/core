<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PostFlightDirectionConsequence extends AbstractFlightConsequence
{
    private FlightSignatureCreatorInterface $flightSignatureCreator;

    private UpdateFlightDirectionInterface $updateFlightDirection;

    public function __construct(
        FlightSignatureCreatorInterface $flightSignatureCreator,
        UpdateFlightDirectionInterface $updateFlightDirection
    ) {
        $this->flightSignatureCreator = $flightSignatureCreator;
        $this->updateFlightDirection = $updateFlightDirection;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
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
            if (!$wrapper->get()->isTractored()) {
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
