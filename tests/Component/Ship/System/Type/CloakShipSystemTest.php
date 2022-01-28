<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class CloakShipSystemTest extends StuTestCase
{

    /**
     * @var null|CloakShipSystem
     */
    private $system;

    public function setUp(): void
    {
        $this->system = new CloakShipSystem();
    }

    public function testCheckActivationConditionsReturnsFalseIfCloakIsActivated(): void
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

    public function testCheckActivationConditionsReturnsFalseIfNotCloakable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isCloakable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

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
        $ship->shouldReceive('isCloakable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertTrue(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testGetEnergyUserForActivationReturnsValues(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('deactivateTractorBeam')
            ->withNoArgs()
            ->once()
            ->andReturnSelf();
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $ship->shouldReceive('setShieldState')
            ->with(false)
            ->once()
            ->andReturnSelf();
        $ship->shouldReceive('setCloakState')
            ->with(true)
            ->once()
            ->andReturnSelf();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setCloakState')
            ->with(false)
            ->once()
            ->andReturnSelf();

        $this->system->deactivate($ship);
    }
}
