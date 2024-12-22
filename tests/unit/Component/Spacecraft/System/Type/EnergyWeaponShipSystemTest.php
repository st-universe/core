<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\StuTestCase;

class EnergyWeaponShipSystemTest extends StuTestCase
{
    private EnergyWeaponShipSystem $system;

    private ShipInterface $ship;
    private ShipWrapperInterface $wrapper;

    #[Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new EnergyWeaponShipSystem();
    }

    public function testCheckActivationConditionsReturnFalseIfCloaked(): void
    {
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Tarnung aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnFalseIfAlertGreen(): void
    {
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Alarmstufe Grün ist', $reason);
    }

    public function testCheckActivationConditionsReturnTrueIfActivateable(): void
    {
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $reason = '';
        $this->assertTrue(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEmpty($reason);
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
        $managerMock = $this->mock(SpacecraftSystemManagerInterface::class);
        $system = $this->mock(SpacecraftSystemInterface::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(SpacecraftSystemInterface::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
