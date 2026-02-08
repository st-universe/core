<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\ComputerSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\StuTestCase;

class SpacecraftShutdownTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&LeaveFleetInterface $leaveFleet;
    private MockInterface&SpacecraftStateChangerInterface $spacecraftStateChanger;
    private MockInterface&ShipUndockingInterface $shipUndocking;

    private SpacecraftShutdownInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);

        $this->subject = new SpacecraftShutdown(
            $this->spacecraftSystemManager,
            $this->leaveFleet,
            $this->spacecraftStateChanger,
            $this->shipUndocking
        );
    }

    public static function parameterDataProvider(): array
    {
        return [[null], [true], [false]];
    }

    #[DataProvider('parameterDataProvider')]
    public function testShutdownWhenShip(?bool $doLeaveFleet): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);
        $ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->spacecraftSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();

        if ($doLeaveFleet === true) {
            $this->leaveFleet->shouldReceive('leaveFleet')
                ->with($ship)
                ->once();
        }

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($wrapper, SpacecraftStateEnum::NONE)
            ->once();

        if ($doLeaveFleet !== null) {
            $this->subject->shutdown($wrapper, $doLeaveFleet);
        } else {
            $this->subject->shutdown($wrapper);
        }
    }

    #[DataProvider('parameterDataProvider')]
    public function testShutdownWhenStation(?bool $doLeaveFleet): void
    {
        $wrapper = $this->mock(StationWrapperInterface::class);
        $station = $this->mock(Station::class);
        $computer = $this->mock(ComputerSystemData::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($station);
        $wrapper->shouldReceive('getComputerSystemDataMandatory->setAlertStateGreen')
            ->withNoArgs()
            ->once()
            ->andReturn($computer);

        $computer->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);
        $station->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($station)
            ->once();

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($wrapper, SpacecraftStateEnum::NONE)
            ->once();

        if ($doLeaveFleet !== null) {
            $this->subject->shutdown($wrapper, $doLeaveFleet);
        } else {
            $this->subject->shutdown($wrapper);
        }
    }

    public function testShutdownExpectNoStateChangeWhenPassiveState(): void
    {
        $wrapper = $this->mock(StationWrapperInterface::class);
        $station = $this->mock(Station::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($station);

        $station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::RETROFIT);
        $station->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->spacecraftSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($station)
            ->once();

        $this->subject->shutdown($wrapper);
    }
}
