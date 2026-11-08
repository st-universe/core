<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereBehaviorTest extends StuTestCase
{
    /** @return iterable<string, array{BorgSpherePhaseEnum}> */
    public static function phases(): iterable
    {
        yield 'arrival in colony orbit' => [BorgSpherePhaseEnum::APPROACH];
        yield 'roaming after player contact' => [BorgSpherePhaseEnum::ROAMING];
    }

    #[DataProvider('phases')]
    public function testMovementNeverSelectsShipsForAnInitiativeAttack(BorgSpherePhaseEnum $phase): void
    {
        $systemMaps = $this->mock(StarSystemMapRepositoryInterface::class);
        $systemMaps->shouldReceive('getByCoordinates')->times(4)->andReturn(null);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldNotReceive('wrapShip', 'wrapSpacecraft');
        $flight = new BorgSphereFlight($this->mock(ShipMoverInterface::class), $this->mock(FlightRouteFactoryInterface::class), $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $this->mock(DistributedMessageSenderInterface::class));
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $systemMaps, $wrappers, $flight, $this->mock(StuRandom::class));
        $location = $this->mock(StarSystemMap::class);
        $location->shouldNotReceive('getSpacecrafts');
        $location->shouldReceive('getX', 'getY')->andReturn(2);
        $location->shouldReceive('getSystemId')->andReturn(1);
        $system = $this->mock(StarSystem::class);
        $system->shouldReceive('getMap')->andReturn(null);
        $location->shouldReceive('getSystem')->andReturn($system);
        $colony = $this->mock(Colony::class);
        $colony->shouldReceive('getStarsystemMap')->andReturn($location);
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setColony($colony))->setPhase($phase);
        $ship = $this->mock(Ship::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $condition->shouldReceive('isDisabled', 'isDestroyed')->andReturn(false);
        $ship->shouldReceive('getCondition')->andReturn($condition);
        $ship->shouldReceive('getLocation')->andReturn($location);
        new BorgSphereBehavior($navigation)->work($event, $ship);
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
        self::assertFalse($event->isInitialAttackDone());
    }
}
