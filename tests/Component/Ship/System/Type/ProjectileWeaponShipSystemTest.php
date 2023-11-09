<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class ProjectileWeaponShipSystemTest extends StuTestCase
{
    /**
     * @var ProjectileWeaponShipSystem|null
     */
    private $system;

    private ShipWrapperInterface $wrapper;
    private ShipInterface $ship;

    public function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new ProjectileWeaponShipSystem();
    }

    public function testCheckActivationConditionsReturnsFalseIfNoTorpedosPresent(): void
    {
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('keine Torpedos vorhanden sind', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfCloaked(): void
    {
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Tarnung aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfAlertGreen(): void
    {
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertNull($reason);
    }

    public function testCheckActivationConditionsTrueIfActivateable(): void
    {
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
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
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDectivateDectivates(): void
    {
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
