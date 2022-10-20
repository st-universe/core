<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class EnergyWeaponShipSystemTest extends StuTestCase
{

    /**
     * @var null|EnergyWeaponShipSystem
     */
    private $system;

    public function setUp(): void
    {
        $this->system = new EnergyWeaponShipSystem();
    }

    public function testCheckActivationConditionsReturnFalseIfNoWeaponAvailable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getPhaserState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnFalseIfCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getPhaserState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getPhaserState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

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

        $ship->shouldReceive('setPhaser')
            ->with(true)
            ->once()
            ->andReturnSelf();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setPhaser')
            ->with(false)
            ->once()
            ->andReturnSelf();

        $this->system->deactivate($ship);
    }
}
