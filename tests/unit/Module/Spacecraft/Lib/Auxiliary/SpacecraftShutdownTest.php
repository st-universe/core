<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftShutdownTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spacecraftRepository;
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private $spacecraftSystemManager;
    /** @var MockInterface&LeaveFleetInterface */
    private $leaveFleet;
    /** @var MockInterface&SpacecraftStateChangerInterface */
    private $spacecraftStateChanger;
    /** @var MockInterface&ShipUndockingInterface */
    private $shipUndocking;

    private SpacecraftShutdownInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);

        $this->subject = new SpacecraftShutdown(
            $this->spacecraftRepository,
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
        $ship = $this->mock(ShipInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);

        $ship->shouldReceive('setAlertStateGreen')
            ->withNoArgs()
            ->once();

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

        $this->spacecraftRepository->shouldReceive('save')
            ->with($ship)
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
        $station = $this->mock(StationInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($station);

        $station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);

        $station->shouldReceive('setAlertStateGreen')
            ->withNoArgs()
            ->once();

        $this->spacecraftSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($station)
            ->once();

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($wrapper, SpacecraftStateEnum::NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($station)
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
        $station = $this->mock(StationInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($station);

        $station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::RETROFIT);

        $station->shouldReceive('setAlertStateGreen')
            ->withNoArgs()
            ->once();

        $this->spacecraftSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($station)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($station)
            ->once();

        $this->subject->shutdown($wrapper);
    }
}
