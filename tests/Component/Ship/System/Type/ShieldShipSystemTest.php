<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\Location;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class ShieldShipSystemTest extends StuTestCase
{
    private ShieldShipSystem $system;

    /**
     * @var ShipStateChangerInterface|MockInterface
     */
    private ShipStateChangerInterface $shipStateChanger;

    private ShipInterface $ship;
    private ShipWrapperInterface $wrapper;

    #[Override]
    public function setUp(): void
    {
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new ShieldShipSystem($this->shipStateChanger);
    }

    public function testCheckActivationConditionsReturnsFalsIfCloaked(): void
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

    public function testCheckActivationConditionsReturnsFalseIfTractoring(): void
    {
        $this->ship->shouldReceive('getCloakState')
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
        $this->ship->shouldReceive('getCloakState')
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
        $this->ship->shouldReceive('getCloakState')
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
        $this->ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Schildemitter erschöpft sind', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfSubspaceEllipseIsExistent(): void
    {
        $location = $this->mock(Location::class);

        $this->ship->shouldReceive('getCloakState')
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
        $this->ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);

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

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $location = $this->mock(Location::class);

        $this->ship->shouldReceive('getCloakState')
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
        $this->ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);

        $location->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::SUBSPACE_ELLIPSE)
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
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
