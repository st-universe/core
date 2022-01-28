<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ShieldShipSystemTest extends StuTestCase
{
    /**
     * @var ShieldShipSystem|null
     */
    private $system;

    public function setUp(): void
    {
        $this->system = new ShieldShipSystem();
    }

    public function testCheckActivationConditionsReturnsFalsIfCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsFalsIfShielsAreActive(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getShieldState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfTraktorBeamIsActive(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getShieldState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfShieldsAreDepleted(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getShieldState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getShieldState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->assertTrue(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testGetEnergyUsageForActivationReturnsValus(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('cancelRepair')
            ->withNoArgs()
            ->once();
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship->shouldReceive('setShieldState')
            ->with(true)
            ->once();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setShieldState')
            ->with(false)
            ->once();

        $this->system->deactivate($ship);
    }
}
