<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use Mockery\MockInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class StopEmergencyTest extends StuTestCase
{
    /** @var ShipLoaderInterface&MockInterface */
    private MockInterface $shipLoader;

    /** @var ShipStateChangerInterface&MockInterface */
    private MockInterface $shipStateChanger;

    /** @var StopEmergencyRequestInterface&MockInterface */
    private MockInterface $stopEmergencyRequest;

    private StopEmergency $subject;

    protected function setUp(): void
    {
        $this->shipLoader = $this->mock(ShipLoaderInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->stopEmergencyRequest = $this->mock(StopEmergencyRequestInterface::class);

        $this->subject = new StopEmergency(
            $this->shipLoader,
            $this->shipStateChanger,
            $this->stopEmergencyRequest
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

        $ship->shouldReceive('isInEmergency')
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

        $ship->shouldReceive('isInEmergency')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($shipWrapper, ShipStateEnum::SHIP_STATE_NONE)
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
