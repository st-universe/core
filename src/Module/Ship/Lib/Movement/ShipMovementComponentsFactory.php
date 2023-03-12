<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\Ship\Lib\Movement\Component\FlightSignatureCreator;
use Stu\Module\Ship\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminator;
use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminatorInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

/**
 * Factory for creating components related to the ship movement process
 */
final class ShipMovementComponentsFactory implements ShipMovementComponentsFactoryInterface
{
    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function createShipMovementBlockingDeterminator(): ShipMovementBlockingDeterminatorInterface
    {
        return new ShipMovementBlockingDeterminator();
    }

    public function createFlightSignatureCreator(): FlightSignatureCreatorInterface
    {
        return new FlightSignatureCreator(
            $this->flightSignatureRepository
        );
    }
}
