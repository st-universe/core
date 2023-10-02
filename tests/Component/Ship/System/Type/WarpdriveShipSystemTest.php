<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

//TODO@hux test handleDamage + handleDestruction
class WarpdriveShipSystemTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ShipRepositoryInterface
     */
    private $shipRepositoryMock;

    /**
     * @var null|MockInterface|ShipStateChangerInterface
     */
    private $shipStateChanger;


    /**
     * @var null|MockInterface|ShipSystemManagerInterface
     */
    private $managerMock;

    /**
     * @var null|WarpdriveShipSystem
     */
    private $system;

    private ShipInterface $ship;
    private ShipWrapperInterface $wrapper;

    public function setUp(): void
    {
        $this->shipRepositoryMock = $this->mock(ShipRepositoryInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->managerMock = $this->mock(ShipSystemManagerInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->system = new WarpdriveShipSystem(
            $this->shipRepositoryMock,
            $this->shipStateChanger
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfShipIsTractored(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->ship, $reason)
        );

        $this->assertEquals('es von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShipInActiveTholianWeb(): void
    {
        $tholianWeb = $this->mock(TholianWebInterface::class);

        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->twice()
            ->andReturn($tholianWeb);
        $tholianWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->ship, $reason)
        );

        $this->assertEquals('es in einem Energienetz gefangen ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShipInWormhole(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->ship, $reason)
        );

        $this->assertEquals('es sich in einem Wurmloch befindet', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfWarpcoreDestroyed(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isSystemHealthy')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPCORE)
            ->once()
            ->andReturn(false);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->ship, $reason)
        );

        $this->assertEquals('der Warpkern zerstört ist', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('isSystemHealthy')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPCORE)
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($this->ship, $reason)
        );

        $this->assertNull($reason);
    }

    public function testGetEnergyUsageForActivationReturnsValue(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivatesAndActivatesWarpStateOnTraktorShip(): void
    {
        $system = $this->mock(ShipSystemInterface::class);
        $traktorBeamShipWrapper = $this->mock(ShipWrapperInterface::class);

        //DOCKING STUFF
        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $this->ship->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection([]));

        //SYSTEM ACTIVATION
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($traktorBeamShipWrapper);

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();
        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($traktorBeamShipWrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->system->activate($this->wrapper, $this->managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->deactivate($this->wrapper);
    }
}
