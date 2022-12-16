<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
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

    public function testCheckActivationConditionsReturnFalseIfCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('die Tarnung aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnFalseIfAlertGreen(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('die Alarmstufe Grün ist', $reason);
    }

    public function testCheckActivationConditionsReturnTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertNull($reason);
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
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($ship, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($ship);
    }
}
