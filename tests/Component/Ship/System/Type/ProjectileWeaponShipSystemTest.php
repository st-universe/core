<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
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

    public function testCheckActivationConditionsReturnsFalseIfNoTorpedosPresent(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('keine Torpedos vorhanden sind', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('die Tarnung aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfAlertGreen(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertNull($reason);
    }

    public function testCheckActivationConditionsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('die Alarmstufe Grün ist', $reason);
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
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($ship);
    }

    public function testDectivateDectivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($ship);
    }
}
