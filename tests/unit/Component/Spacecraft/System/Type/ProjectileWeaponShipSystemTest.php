<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

class ProjectileWeaponShipSystemTest extends StuTestCase
{
    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;
    private ProjectileWeaponShipSystem $system;

    #[Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(Ship::class);
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

        $reason = '';
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

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
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

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->wrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $reason = '';
        $this->assertTrue(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEmpty($reason);
    }

    public function testCheckActivationConditionsTrueIfActivateable(): void
    {
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->wrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
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
        $managerMock = $this->mock(SpacecraftSystemManagerInterface::class);
        $system = $this->mock(SpacecraftSystem::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDectivateDectivates(): void
    {
        $system = $this->mock(SpacecraftSystem::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
