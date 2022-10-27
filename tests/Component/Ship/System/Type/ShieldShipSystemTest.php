<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class ShieldShipSystemTest extends StuTestCase
{
    /**
     * @var ShieldShipSystem|null
     */
    private $system;

    public function setUp(): void
    {
        $this->system = new ShieldShipSystem();
    }

    public function testCheckActivationConditionsReturnsFalsIfCloaked(): void
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

    public function testCheckActivationConditionsReturnsFalseIfTractoring(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('der Traktorstrahl aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfTractored(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('das Schiff von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShieldsAreDepleted(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );
        $this->assertEquals('die Schildemitter erschöpft sind', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

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
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('cancelRepair')
            ->withNoArgs()
            ->once();
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($ship);
    }
}
