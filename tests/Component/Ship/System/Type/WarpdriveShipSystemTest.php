<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Mockery\MockInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class WarpdriveShipSystemTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ShipRepositoryInterface
     */
    private $shipRepository;

    /**
     * @var null|WarpdriveShipSystem
     */
    private $system;

    public function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);

        $this->system = new WarpdriveShipSystem(
            $this->shipRepository
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfAlreadyActive(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfHoldByTraktorbeam(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));
        $ship->shouldReceive('')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->assertTrue(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testGetEnergyUsageForActivationReturnsValue(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivatesAndDisablesTraktorbeamOnEnergyShortage(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('cancelRepair')
            ->withNoArgs()
            ->once();
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship->shouldReceive('setWarpState')
            ->with(true)
            ->once();
        $ship->shouldReceive('traktorBisTractoringeamFromShip')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('deactivateTractorBeam')
            ->withNoArgs()
            ->once();

        $this->system->activate($ship);
    }

    public function testActivateActivatesAndActivatesWarpStateOnTraktorShip(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $traktorBeamShip = $this->mock(ShipInterface::class);

        $ship->shouldReceive('cancelRepair')
            ->withNoArgs()
            ->once();
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship->shouldReceive('setWarpState')
            ->with(true)
            ->once();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->twice()
            ->andReturn(2);
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($traktorBeamShip);
        $ship->shouldReceive('setEps')
            ->with(1)
            ->once();

        $traktorBeamShip->shouldReceive('cancelRepair')
            ->withNoArgs()
            ->once();
        $traktorBeamShip->shouldReceive('setWarpState')
            ->with(true)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($traktorBeamShip)
            ->once();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $traktorBeamShip = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($traktorBeamShip);
        $ship->shouldReceive('setWarpState')
            ->with(false)
            ->once();

        $traktorBeamShip->shouldReceive('setWarpState')
            ->with(false)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($traktorBeamShip)
            ->once();

        $this->system->deactivate($ship);
    }
}
