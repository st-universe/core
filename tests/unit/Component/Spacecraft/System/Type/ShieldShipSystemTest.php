<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery\MockInterface;
use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

class ShieldShipSystemTest extends StuTestCase
{
    private ShieldShipSystem $system;

    private SpacecraftStateChangerInterface&MockInterface $spacecraftStateChanger;

    private Ship&MockInterface $ship;
    private ShipWrapperInterface&MockInterface $wrapper;

    #[Override]
    public function setUp(): void
    {
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new ShieldShipSystem($this->spacecraftStateChanger);
    }

    public function testCheckActivationConditionsReturnsFalseIfCloaked(): void
    {
        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Tarnung aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfTractoring(): void
    {
        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('der Traktorstrahl aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfTractored(): void
    {
        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('das Schiff von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShieldsAreDepleted(): void
    {
        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Schildemitter erschöpft sind', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShieldMalfunctionEffect(): void
    {
        $location = $this->mock(Map::class);
        $fieldType = $this->mock(MapFieldType::class);

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $location->shouldReceive('getFieldType')
            ->withNoArgs()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::SHIELD_MALFUNCTION)
            ->once()
            ->andReturn(true);
        $fieldType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('NEBEL');

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('"NEBEL" es verhindert', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfSubspaceEllipseIsExistent(): void
    {
        $location = $this->mock(Map::class);
        $fieldType = $this->mock(MapFieldType::class);

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $location->shouldReceive('getFieldType')
            ->withNoArgs()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::SHIELD_MALFUNCTION)
            ->once()
            ->andReturn(false);

        $location->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::SUBSPACE_ELLIPSE)
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('in diesem Sektor eine Subraumellipse vorhanden ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfIonStormIsExistent(): void
    {
        $location = $this->mock(Map::class);
        $fieldType = $this->mock(MapFieldType::class);

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $location->shouldReceive('getFieldType')
            ->withNoArgs()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::SHIELD_MALFUNCTION)
            ->once()
            ->andReturn(false);

        $location->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::SUBSPACE_ELLIPSE)
            ->once()
            ->andReturn(false);
        $location->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('in diesem Sektor ein Ionensturm tobt', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $location = $this->mock(Map::class);
        $fieldType = $this->mock(MapFieldType::class);

        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($location);

        $location->shouldReceive('getFieldType')
            ->withNoArgs()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::SHIELD_MALFUNCTION)
            ->once()
            ->andReturn(false);

        $location->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::SUBSPACE_ELLIPSE)
            ->once()
            ->andReturn(false);
        $location->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(false);

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
        $system = $this->mock(SpacecraftSystem::class);

        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
            ->once();

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($this->wrapper, SpacecraftStateEnum::NONE)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(SpacecraftSystem::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
