<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement;

use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminator;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\StuTestCase;

class ShipMovementComponentsFactoryTest extends StuTestCase
{
    /** @var MockInterface&FlightSignatureRepositoryInterface */
    private MockInterface $flightSignatureRepository;

    private ShipMovementComponentsFactory $subject;

    protected function setUp(): void
    {
        $this->flightSignatureRepository = $this->mock(FlightSignatureRepositoryInterface::class);

        $this->subject = new ShipMovementComponentsFactory(
            $this->flightSignatureRepository
        );
    }

    public function testCreateShipMovementBlockingDeterminatorReturnsInstance(): void
    {
        $this->assertInstanceOf(
            ShipMovementBlockingDeterminator::class,
            $this->subject->createShipMovementBlockingDeterminator()
        );
    }
}
