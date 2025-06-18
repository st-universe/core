<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapFieldTypeInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\StuTestCase;

class CloakShipSystemTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftStateChangerInterface */
    private $spacecraftStateChanger;
    /** @var MockInterface&ShipInterface */
    private $ship;
    /** @var MockInterface&ShipWrapperInterface */
    private $wrapper;

    private CloakShipSystem $system;

    #[Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new CloakShipSystem(
            $this->spacecraftStateChanger
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfTractoring(): void
    {
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('das Schiff den Traktorstrahl aktiviert hat', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfTractored(): void
    {
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('das Schiff von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfEffectExistent(): void
    {
        $map = $this->mock(MapInterface::class);
        $mapFieldType = $this->mock(MapFieldTypeInterface::class);

        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $map->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($mapFieldType);

        $mapFieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::CLOAK_UNUSEABLE)
            ->once()
            ->andReturn(true);
        $mapFieldType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('NEBEL');

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('"NEBEL" es verhindert', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfSubspaceActive(): void
    {
        $map = $this->mock(MapInterface::class);
        $mapFieldType = $this->mock(MapFieldTypeInterface::class);

        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $map->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($mapFieldType);

        $mapFieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::CLOAK_UNUSEABLE)
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('getSubspaceState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('die Subraumfeldsensoren aktiv sind', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfAlertRed(): void
    {
        $map = $this->mock(MapInterface::class);
        $mapFieldType = $this->mock(MapFieldTypeInterface::class);

        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $map->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($mapFieldType);

        $mapFieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::CLOAK_UNUSEABLE)
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('getSubspaceState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('die Alarmstufe Rot ist', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $map = $this->mock(MapInterface::class);
        $mapFieldType = $this->mock(MapFieldTypeInterface::class);

        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $map->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($mapFieldType);

        $mapFieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::CLOAK_UNUSEABLE)
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('getSubspaceState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);

        $reason = '';
        $this->assertTrue(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEmpty($reason);
    }

    public function testGetEnergyUserForActivationReturnsValues(): void
    {
        $this->assertSame(
            10,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivates(): void
    {
        $managerMock = $this->mock(SpacecraftSystemManagerInterface::class);
        $systemCloak = $this->mock(SpacecraftSystemInterface::class);

        //OTHER
        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($this->wrapper, SpacecraftStateEnum::NONE)
            ->once();

        //SYSTEMS TO SHUTDOWN
        $systemTypes = [
            SpacecraftSystemTypeEnum::ASTRO_LABORATORY->value => $this->mock(SpacecraftSystemInterface::class),
            SpacecraftSystemTypeEnum::SHIELDS->value => $this->mock(SpacecraftSystemInterface::class),
            SpacecraftSystemTypeEnum::PHASER->value => $this->mock(SpacecraftSystemInterface::class),
            SpacecraftSystemTypeEnum::TORPEDO->value => $this->mock(SpacecraftSystemInterface::class),
        ];
        foreach ($systemTypes as $systemType => $system) {
            $this->ship->shouldReceive('hasSpacecraftSystem')
                ->with(SpacecraftSystemTypeEnum::from($systemType))
                ->once()
                ->andReturnTrue();
            $this->ship->shouldReceive('getSpacecraftSystem')
                ->with(SpacecraftSystemTypeEnum::from($systemType))
                ->once()
                ->andReturn($system);
            $system->shouldReceive('setMode')
                ->with(SpacecraftSystemModeEnum::MODE_OFF)
                ->once();
        }

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::CLOAK)
            ->once()
            ->andReturn($systemCloak);
        $systemCloak->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
            ->once();
        $managerMock->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(SpacecraftSystemInterface::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::CLOAK)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
