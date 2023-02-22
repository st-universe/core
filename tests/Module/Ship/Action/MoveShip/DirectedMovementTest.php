<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class DirectedMovementTest extends StuTestCase
{
    /** @var MockInterface&MoveShipRequestInterface */
    private MockInterface $moveShipRequest;

    /** @var MockInterface&ShipLoaderInterface */
    private MockInterface $shipLoader;

    /** @var MockInterface&ShipMoverInterface */
    private MockInterface $shipMover;

    protected function setUp(): void
    {
        $this->moveShipRequest = $this->mock(MoveShipRequestInterface::class);
        $this->shipLoader = $this->mock(ShipLoaderInterface::class);
        $this->shipMover = $this->mock(ShipMoverInterface::class);
    }

    public static function moveDataProvider(): array
    {
        return [
            [MoveShipDown::class, 3, 4, 3, 6, 2],
            [MoveShipUp::class, 3, 4, 3, 2, 2],
            [MoveShipUp::class, 3, 4, 3, 1, 9],
            [MoveShipLeft::class, 3, 4, 1, 4, 9],
            [MoveShipLeft::class, 3, 4, 2, 4, 1],
            [MoveShipRight::class, 3, 4, 6, 4, 3],
        ];
    }

    /**
     * @dataProvider moveDataProvider
     *
     * @param class-string $className
     */
    public function testHandle(
        string $className,
        int $shipPosX,
        int $shipPosY,
        int $destX,
        int $destY,
        int $fieldCount
    ): void {
        $userId = 666;
        $shipId = 42;
        $message = 'some-message';

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        /** @var AbstractDirectedMovement $subject */
        $subject = new $className(
            $this->moveShipRequest,
            $this->shipLoader,
            $this->shipMover
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);
        $this->moveShipRequest->shouldReceive('getFieldCount')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldCount);

        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn($shipPosX);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn($shipPosY);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->shipMover->shouldReceive('checkAndMove')
            ->with(
                $shipWrapper,
                $destX,
                $destY
            )
            ->once();
        $this->shipMover->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn([$message]);

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformationMerge')
            ->with([$message])
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();

        $subject->handle($game);

        static::assertTrue(
            $subject->performSessionCheck()
        );
    }

    public function testHandleEndsIfDestroyed(): void
    {
        $userId = 666;
        $shipId = 42;
        $message = 'some-message';
        $shipPosX = 5;
        $shipPosY = 5;
        $destX = 6;
        $destY = 5;
        $fieldCount = 1;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->shipLoader,
            $this->shipMover
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);
        $this->moveShipRequest->shouldReceive('getFieldCount')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldCount);

        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn($shipPosX);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn($shipPosY);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->shipMover->shouldReceive('checkAndMove')
            ->with(
                $shipWrapper,
                $destX,
                $destY
            )
            ->once();
        $this->shipMover->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn([$message]);

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformationMerge')
            ->with([$message])
            ->once();

        $subject->handle($game);

        static::assertTrue(
            $subject->performSessionCheck()
        );
    }

    public function testHandleMovesWithCoordinatesFromRequest(): void
    {
        $userId = 666;
        $shipId = 42;
        $message = 'some-message';
        $destX = 6;
        $destY = 5;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $subject = new MoveShip(
            $this->moveShipRequest,
            $this->shipLoader,
            $this->shipMover
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);
        $this->moveShipRequest->shouldReceive('getDestinationPosX')
            ->withNoArgs()
            ->once()
            ->andReturn($destX);
        $this->moveShipRequest->shouldReceive('getDestinationPosY')
            ->withNoArgs()
            ->once()
            ->andReturn($destY);

        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->shipMover->shouldReceive('checkAndMove')
            ->with(
                $shipWrapper,
                $destX,
                $destY
            )
            ->once();
        $this->shipMover->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn([$message]);

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformationMerge')
            ->with([$message])
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();

        $subject->handle($game);
    }
}
