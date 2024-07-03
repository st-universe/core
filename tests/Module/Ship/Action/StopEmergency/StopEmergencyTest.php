<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use Override;
use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;
use Stu\StuTestCase;

class StopEmergencyTest extends StuTestCase
{
    /** @var ShipLoaderInterface&MockInterface */
    private MockInterface $shipLoader;

    /** @var StopEmergencyRequestInterface&MockInterface */
    private MockInterface $stopEmergencyRequest;

    /** @var MockInterface|SpacecraftEmergencyRepositoryInterface */
    private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository;

    private StopEmergency $subject;

    /** @var MockInterface|StuTime */
    private StuTime $stuTime;

    #[Override]
    protected function setUp(): void
    {
        $this->shipLoader = $this->mock(ShipLoaderInterface::class);
        $this->spacecraftEmergencyRepository = $this->mock(SpacecraftEmergencyRepositoryInterface::class);
        $this->stopEmergencyRequest = $this->mock(StopEmergencyRequestInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new StopEmergency(
            $this->shipLoader,
            $this->spacecraftEmergencyRepository,
            $this->stopEmergencyRequest,
            $this->stuTime
        );
    }

    public function testHandleDoesNothingIfEmergencyCallIsNotActive(): void
    {
        $shipId = 666;
        $userId = 42;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $user = $this->mock(UserInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->stopEmergencyRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('getIsInEmergency')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->subject->handle(
            $game
        );
    }

    public function testHandleStopsEmergency(): void
    {
        $shipId = 666;
        $userId = 42;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $user = $this->mock(UserInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $emergency = $this->mock(SpacecraftEmergencyInterface::class);

        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('addInformation')
            ->with('Das Notrufsignal wurde beendet')
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->stopEmergencyRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('getIsInEmergency')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $ship->shouldReceive('setIsInEmergency')
            ->with(false)
            ->once();

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $this->spacecraftEmergencyRepository->shouldReceive('getByShipId')
            ->with($shipId)
            ->once()
            ->andReturn($emergency);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $emergency->shouldReceive('setDeleted')
            ->with(3)
            ->once();

        $this->spacecraftEmergencyRepository->shouldReceive('save')
            ->with($emergency)
            ->once();

        $this->subject->handle(
            $game
        );
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        static::assertTrue(
            $this->subject->performSessionCheck()
        );
    }
}
