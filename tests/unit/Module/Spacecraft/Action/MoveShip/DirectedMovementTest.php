<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

class DirectedMovementTest extends StuTestCase
{
    /** @var MockInterface&MoveShipRequestInterface */
    private $moveShipRequest;
    /** @var MockInterface&SpacecraftLoaderInterface */
    private $spacecraftLoader;
    /** @var MockInterface&ShipMoverInterface */
    private $shipMover;
    /** @var MockInterface&FlightRouteFactoryInterface */
    private $flightRouteFactory;
    /** @var MockInterface&RandomSystemEntryInterface */
    private $randomSystemEntry;
    /** @var MockInterface&DistributedMessageSenderInterface */
    private $distributedMessageSender;

    #[Override]
    protected function setUp(): void
    {
        $this->moveShipRequest = $this->mock(MoveShipRequestInterface::class);
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->shipMover = $this->mock(ShipMoverInterface::class);
        $this->flightRouteFactory = $this->mock(FlightRouteFactoryInterface::class);
        $this->randomSystemEntry = $this->mock(RandomSystemEntryInterface::class);
        $this->distributedMessageSender = $this->mock(DistributedMessageSenderInterface::class);
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
     * @param class-string $className
     */
    #[DataProvider('moveDataProvider')]
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

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $holdingWeb = $this->mock(TholianWebInterface::class);

        /** @var AbstractDirectedMovement $subject */
        $subject = new $className(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
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
        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn($holdingWeb);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);

        $holdingWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->flightRouteFactory->shouldReceive('getRouteForCoordinateDestination')
            ->with($ship, $destX, $destY)
            ->once()
            ->andReturn($flightRoute);

        $this->shipMover->shouldReceive('checkAndMove')
            ->with(
                $shipWrapper,
                $flightRoute
            )
            ->once()
            ->andReturn($messages);

        $messages->shouldReceive('getInformationDump')
            ->withNoArgs()
            ->once()
            ->andReturn($informationWrapper);

        $this->distributedMessageSender->shouldReceive('distributeMessageCollection')
            ->with($messages, $userId, PrivateMessageFolderTypeEnum::SPECIAL_SHIP)
            ->once();

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformationWrapper')
            ->with($informationWrapper)
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $subject->handle($game);

        static::assertTrue(
            $subject->performSessionCheck()
        );
    }

    public function testHandleExpectNoMovementWhenNotEnoughCrew(): void
    {
        $userId = 666;
        $shipId = 42;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnFalse();

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $subject->handle($game);
    }

    public function testHandleExpectNoMovementWhenShipIsTractored(): void
    {
        $userId = 666;
        $shipId = 42;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn(null);

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformation')
            ->with('Das Schiff wird von einem Traktorstrahl gehalten')
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $subject->handle($game);
    }

    public function testHandleExpectNoMovementWhenShipIsInWeb(): void
    {
        $userId = 666;
        $shipId = 42;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $holdingWeb = $this->mock(TholianWebInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn($holdingWeb);

        $holdingWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformation')
            ->with('Das Schiff ist in einem Energienetz gefangen')
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();


        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $subject->handle($game);
    }

    public function testHandleExpectNoMovementWhenFleetIsDefending(): void
    {
        $userId = 666;
        $shipId = 42;

        $ship = $this->mock(ShipInterface::class);
        $fleet = $this->mock(FleetInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($fleet);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $fleet->shouldReceive('getDefendedColony')
            ->withNoArgs()
            ->andReturn($this->mock(ColonyInterface::class));

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformation')
            ->with('Flug während Kolonie-Verteidigung nicht möglich')
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $subject->handle($game);
    }

    public function testHandleExpectNoMovementWhenFleetIsBlocking(): void
    {
        $userId = 666;
        $shipId = 42;

        $ship = $this->mock(ShipInterface::class);
        $fleet = $this->mock(FleetInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
        );

        $this->moveShipRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($fleet);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $fleet->shouldReceive('getDefendedColony')
            ->withNoArgs()
            ->andReturn(null);
        $fleet->shouldReceive('getBlockedColony')
            ->withNoArgs()
            ->andReturn($this->mock(ColonyInterface::class));

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformation')
            ->with('Flug während Kolonie-Blockierung nicht möglich')
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $subject->handle($game);
    }

    public function testHandleEndsIfDestroyed(): void
    {
        $userId = 666;
        $shipId = 42;
        $shipPosX = 5;
        $shipPosY = 5;
        $destX = 6;
        $destY = 5;
        $fieldCount = 1;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);
        $holdingWeb = $this->mock(TholianWebInterface::class);

        $subject = new MoveShipRight(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
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
        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn($holdingWeb);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);

        $holdingWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->flightRouteFactory->shouldReceive('getRouteForCoordinateDestination')
            ->with($ship, $destX, $destY)
            ->once()
            ->andReturn($flightRoute);

        $this->shipMover->shouldReceive('checkAndMove')
            ->with(
                $shipWrapper,
                $flightRoute
            )
            ->once()
            ->andReturn($messages);

        $messages->shouldReceive('getInformationDump')
            ->withNoArgs()
            ->once()
            ->andReturn($informationWrapper);

        $this->distributedMessageSender->shouldReceive('distributeMessageCollection')
            ->with($messages, $userId, PrivateMessageFolderTypeEnum::SPECIAL_SHIP)
            ->once();

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformationWrapper')
            ->with($informationWrapper)
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('setView')
            ->with(ModuleEnum::SHIP)
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
        $informationWrapper = $this->mock(InformationWrapper::class);
        $destX = 6;
        $destY = 5;

        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);
        $holdingWeb = $this->mock(TholianWebInterface::class);

        $subject = new MoveShip(
            $this->moveShipRequest,
            $this->spacecraftLoader,
            $this->shipMover,
            $this->flightRouteFactory,
            $this->randomSystemEntry,
            $this->distributedMessageSender
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
        $ship->shouldReceive('hasEnoughCrew')
            ->with($game)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->andReturn($holdingWeb);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);

        $holdingWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->flightRouteFactory->shouldReceive('getRouteForCoordinateDestination')
            ->with($ship, $destX, $destY)
            ->once()
            ->andReturn($flightRoute);

        $this->shipMover->shouldReceive('checkAndMove')
            ->with(
                $shipWrapper,
                $flightRoute
            )
            ->once()
            ->andReturn($messages);

        $messages->shouldReceive('getInformationDump')
            ->withNoArgs()
            ->once()
            ->andReturn($informationWrapper);

        $this->distributedMessageSender->shouldReceive('distributeMessageCollection')
            ->with($messages, $userId, PrivateMessageFolderTypeEnum::SPECIAL_SHIP)
            ->once();

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('addInformationWrapper')
            ->with($informationWrapper)
            ->once();
        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $subject->handle($game);
    }
}
