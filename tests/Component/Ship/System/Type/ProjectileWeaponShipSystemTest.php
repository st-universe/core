<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ProjectileWeaponShipSystemTest extends StuTestCase
{
    /**
     * @var ProjectileWeaponShipSystem|null
     */
    private $system;

    public function setUp(): void
    {
        $this->system = new ProjectileWeaponShipSystem();
    }

    public function testCheckActivationConditionsReturnsFalseIfAlreadyActive(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfNoTorpedosPresent(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->assertTrue(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testGetEnergyUserForActivationReturnsValus(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setTorpedos')
            ->with(true)
            ->once();

        $this->system->activate($ship);
    }

    public function testDectivateDectivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setTorpedos')
            ->with(false)
            ->once();

        $this->system->deactivate($ship);
    }
}
