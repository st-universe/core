<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgSphereFlightTest extends StuTestCase
{
    private ShipMoverInterface&MockInterface $mover;
    private FlightRouteFactoryInterface&MockInterface $routes;
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private SpacecraftSystemManagerInterface&MockInterface $systems;
    private DistributedMessageSenderInterface&MockInterface $messages;
    private BorgSphereFlight $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->mover = $this->mock(ShipMoverInterface::class);
        $this->routes = $this->mock(FlightRouteFactoryInterface::class);
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->systems = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->messages = $this->mock(DistributedMessageSenderInterface::class);
        $this->subject = new BorgSphereFlight($this->mover, $this->routes, $this->wrappers, $this->systems, $this->messages);
    }

    public function testDisabledSphereCannotMove(): void
    {
        $ship = new Ship();
        $ship->setCondition(new SpacecraftCondition($ship)->setDisabled(true));
        $this->mover->shouldNotReceive('checkAndMove');
        $this->subject->move($ship, new Map());
        self::assertTrue($ship->getCondition()->isDisabled());
    }

    public function testMovementUsesNormalFlightAndAlwaysLeavesWarp(): void
    {
        [$ship, $destination, $wrapper, $route] = $this->flight();
        $messages = $this->mock(MessageCollectionInterface::class);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(19);
        $ship->shouldReceive('getUser')->once()->andReturn($user);
        $this->mover->shouldReceive('checkAndMove')->once()->with($wrapper, $route)->andReturn($messages);
        $this->messages->shouldReceive('distributeMessageCollection')->once()->with($messages, 19, PrivateMessageFolderTypeEnum::SPECIAL_SHIP);
        $this->subject->move($ship, $destination);
    }

    public function testFailedFlightStillLeavesWarp(): void
    {
        [$ship, $destination, $wrapper, $route] = $this->flight();
        $this->mover->shouldReceive('checkAndMove')->once()->with($wrapper, $route)->andThrow(new RuntimeException('flight failed'));
        $this->expectException(RuntimeException::class);
        $this->subject->move($ship, $destination);
    }

    /** @return array{Ship&MockInterface, Map&MockInterface, ShipWrapperInterface&MockInterface, FlightRouteInterface&MockInterface} */
    private function flight(): array
    {
        $ship = $this->mock(Ship::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $condition->shouldReceive('isDisabled')->once()->andReturn(false);
        $condition->shouldReceive('isDestroyed')->once()->andReturn(false);
        $ship->shouldReceive('getCondition')->twice()->andReturn($condition);
        $ship->shouldReceive('getWarpDriveState')->once()->andReturn(true);
        $source = $this->mock(Map::class);
        $destination = $this->mock(Map::class);
        $destination->shouldReceive('getX')->once()->andReturn(2);
        $destination->shouldReceive('getY')->once()->andReturn(3);
        $ship->shouldReceive('getLocation')->once()->andReturn($source);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $this->wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($wrapper);
        $route = $this->mock(FlightRouteInterface::class);
        $this->routes->shouldReceive('getRouteForCoordinateDestination')->once()->with($ship, 2, 3)->andReturn($route);
        $this->systems->shouldReceive('deactivate')->once()->with($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE, true);
        return [$ship, $destination, $wrapper, $route];
    }
}
