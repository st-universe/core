<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class WarpdriveShipSystemTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ShipRepositoryInterface
     */
    private $shipRepositoryMock;

    /**
     * @var null|MockInterface|CancelRepairInterface
     */
    private $cancelRepairMock;

    /**
     * @var null|MockInterface|ShipWrapperFactoryInterface
     */
    private $shipWrapperFactoryMock;

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
        $this->cancelRepairMock = $this->mock(CancelRepairInterface::class);
        $this->managerMock = $this->mock(ShipSystemManagerInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->system = new WarpdriveShipSystem(
            $this->shipRepositoryMock,
            $this->cancelRepairMock
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

    public function testCheckActivationConditionsReturnsFalseIfShipInWormhole(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

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

    public function testActivateActivatesAndDisablesTraktorbeamOnEnergyShortage(): void
    {
        $system = $this->mock(ShipSystemInterface::class);
        $epsSystem = $this->mock(EpsShipSystem::class);

        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsShipSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $dockedShip = Mockery::mock(ShipInterface::class);
        $dockedShipsCollection = new ArrayCollection([$dockedShip]);
        $this->ship->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->twice()
            ->andReturn($dockedShipsCollection);

        $dockedShip->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $this->shipRepositoryMock->shouldReceive('save')
            ->with($dockedShip)
            ->once();
        $this->cancelRepairMock->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();
        $this->managerMock->shouldReceive('deactivate')
            ->with($this->ship, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once();

        $this->system->activate($this->wrapper, $this->managerMock);
        $this->assertTrue($dockedShipsCollection->isEmpty());
    }

    public function testActivateActivatesAndActivatesWarpStateOnTraktorShip(): void
    {
        $system = $this->mock(ShipSystemInterface::class);
        $traktorBeamShip = $this->mock(ShipInterface::class);
        $epsSystem = $this->mock(EpsShipSystem::class);

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
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsShipSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->twice()
            ->andReturn(2);
        $epsSystem->shouldReceive('setEps')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($traktorBeamShip);

        $this->shipRepositoryMock->shouldReceive('save')
            ->with($traktorBeamShip)
            ->once();

        $this->cancelRepairMock->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();
        $this->cancelRepairMock->shouldReceive('cancelRepair')
            ->with($traktorBeamShip)
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

        $this->system->deactivate($this->ship);
    }
}
