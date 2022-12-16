<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Mockery;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class CloakShipSystemTest extends StuTestCase
{

    /**
     * @var null|CloakShipSystem
     */
    private $system;

    /**
     * @var null|AstroEntryLibInterface|MockInterface
     */
    private $astroEntryLib;

    /**
     * @var null|MockInterface|CancelRepairInterface
     */
    private $cancelRepairMock;

    /**
     * @var null|MockInterface|ShipWrapperFactoryInterface
     */
    private $shipWrapperFactoryMock;

    public function setUp(): void
    {
        $this->astroEntryLib = Mockery::mock(AstroEntryLibInterface::class);
        $this->cancelRepairMock = $this->mock(CancelRepairInterface::class);
        $this->shipWrapperFactoryMock = $this->mock(ShipWrapperFactoryInterface::class);

        $this->system = new CloakShipSystem(
            $this->astroEntryLib,
            $this->cancelRepairMock,
            $this->shipWrapperFactoryMock
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfTractoring(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('das Schiff den Traktorstrahl aktiviert hat', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfTractored(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('das Schiff von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfSubspaceActive(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getSubspaceState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('die Subraumfeldsensoren aktiv sind', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfAlertRed(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getSubspaceState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_RED);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('die Alarmstufe Rot ist', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getSubspaceState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertNull($reason);
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
        $ship = $this->mock(ShipInterface::class);
        $wrapperMock = $this->mock(ShipWrapperInterface::class);
        $systemCloak = $this->mock(ShipSystemInterface::class);

        //OTHER
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $this->cancelRepairMock->shouldReceive('cancelRepair')
            ->with($ship)
            ->once();
        $this->shipWrapperFactoryMock->shouldReceive('wrapShip')
            ->with($ship)
            ->once()
            ->andReturn($wrapperMock);
        $wrapperMock->shouldReceive('deactivateTractorBeam')
            ->withNoArgs()
            ->once();

        //ASTRO STUFF
        $ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($ship)
            ->once();

        //SYSTEMS TO SHUTDOWN
        $systemTypes = [
            ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY => $this->mock(ShipSystemInterface::class),
            ShipSystemTypeEnum::SYSTEM_SHIELDS => $this->mock(ShipSystemInterface::class),
            ShipSystemTypeEnum::SYSTEM_PHASER => $this->mock(ShipSystemInterface::class),
            ShipSystemTypeEnum::SYSTEM_TORPEDO => $this->mock(ShipSystemInterface::class)
        ];
        foreach ($systemTypes as $systemType => $system) {
            $ship->shouldReceive('hasShipSystem')
                ->with($systemType)
                ->once()
                ->andReturnTrue();
            $ship->shouldReceive('getShipSystem')
                ->with($systemType)
                ->once()
                ->andReturn($system);
            $system->shouldReceive('setMode')
                ->with(ShipSystemModeEnum::MODE_OFF)
                ->once();
        }

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_CLOAK)
            ->once()
            ->andReturn($systemCloak);
        $systemCloak->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_CLOAK)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($ship);
    }
}
