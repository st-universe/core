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

    public function setUp(): void
    {
        $this->shipRepositoryMock = $this->mock(ShipRepositoryInterface::class);
        $this->cancelRepairMock = $this->mock(CancelRepairInterface::class);
        $this->managerMock = $this->mock(ShipSystemManagerInterface::class);

        $this->system = new WarpdriveShipSystem(
            $this->shipRepositoryMock,
            $this->cancelRepairMock
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfShipIsTractored(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('es von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShipInWormhole(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('es sich in einem Wurmloch befindet', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfWarpcoreDestroyed(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $ship->shouldReceive('isSystemHealthy')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPCORE)
            ->once()
            ->andReturn(false);

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($ship, $reason)
        );

        $this->assertEquals('der Warpkern zerstört ist', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $ship->shouldReceive('isSystemHealthy')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPCORE)
            ->once()
            ->andReturn(true);

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($ship, $reason)
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
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $dockedShip = Mockery::mock(ShipInterface::class);
        $dockedShipsCollection = new ArrayCollection([$dockedShip]);
        $ship->shouldReceive('getDockedShips')
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
            ->with($ship)
            ->once();
        $this->managerMock->shouldReceive('deactivate')
            ->with($ship, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once();

        $this->system->activate($ship, $this->managerMock);
        $this->assertTrue($dockedShipsCollection->isEmpty());
    }

    public function testActivateActivatesAndActivatesWarpStateOnTraktorShip(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);
        $traktorBeamShip = $this->mock(ShipInterface::class);


        //DOCKING STUFF
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $ship->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection([]));

        //SYSTEM ACTIVATION
        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();
        $ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->twice()
            ->andReturn(2);
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($traktorBeamShip);
        $ship->shouldReceive('setEps')
            ->with(1)
            ->once();

        $this->shipRepositoryMock->shouldReceive('save')
            ->with($traktorBeamShip)
            ->once();

        $this->cancelRepairMock->shouldReceive('cancelRepair')
            ->with($ship)
            ->once();
        $this->cancelRepairMock->shouldReceive('cancelRepair')
            ->with($traktorBeamShip)
            ->once();

        $this->system->activate($ship, $this->managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($ship);
    }
}
