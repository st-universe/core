<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
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

    /** @return iterable<string, array{BorgSpherePhaseEnum, int, bool}> */
    public static function movementRolls(): iterable
    {
        foreach ([BorgSpherePhaseEnum::APPROACH, BorgSpherePhaseEnum::ROAMING] as $phase) {
            foreach ([1, 20, 21, 100] as $roll) {
                yield $phase->name . ' roll ' . $roll => [$phase, $roll, $roll > 20];
            }
        }
    }

    #[DataProvider('movementRolls')]
    public function testWithoutTargetsTheIdleChanceControlsApproachAndRoaming(BorgSpherePhaseEnum $phase, int $roll, bool $moves): void
    {
        $checkedTargets = new ArrayCollection();
        $systemMaps = $this->mock(StarSystemMapRepositoryInterface::class);
        if ($moves && $phase === BorgSpherePhaseEnum::ROAMING) {
            $systemMaps->shouldReceive('getByCoordinates')->times(4)->andReturnUsing(static function () use ($checkedTargets): null {
                self::assertFalse($checkedTargets->isEmpty());
                return null;
            });
        } else {
            $systemMaps->shouldNotReceive('getByCoordinates');
        }
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldNotReceive('wrapSpacecraft');
        $flight = new BorgSphereFlight($this->mock(ShipMoverInterface::class), $this->mock(FlightRouteFactoryInterface::class), $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $this->mock(DistributedMessageSenderInterface::class));
        $navigationRandom = $this->mock(StuRandom::class);
        if ($moves) {
            $navigationRandom->shouldReceive('rand')->once()->with(1, 7)->andReturn(1);
        } else {
            $navigationRandom->shouldNotReceive('rand');
        }
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $systemMaps, $wrappers, $flight, $navigationRandom);
        $location = $this->mock(StarSystemMap::class);
        $location->shouldReceive('getSpacecraftsWithoutVacation')->once()->andReturnUsing(static function () use ($checkedTargets): ArrayCollection {
            $checkedTargets->add(true);
            return new ArrayCollection();
        });
        $location->shouldReceive('getX', 'getY')->andReturn(2);
        $location->shouldReceive('getAnomalies')->andReturn(new ArrayCollection());
        $location->shouldReceive('getSystemId')->andReturn(1);
        $system = $this->mock(StarSystem::class);
        $system->shouldReceive('getMap')->andReturn(null);
        $location->shouldReceive('getSystem')->andReturn($system);
        $colony = $this->mock(Colony::class);
        $colony->shouldReceive('getStarsystemMap')->andReturn($location);
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setColony($colony)->setSettings([
            'idle_chance_percent' => 20,
            'movement_min_fields' => 1,
            'movement_max_fields' => 7
        ]))->setPhase($phase);
        $ship = $this->mock(Ship::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $condition->shouldReceive('isDisabled', 'isDestroyed')->andReturn(false);
        $ship->shouldReceive('getCondition')->andReturn($condition);
        $ship->shouldReceive('getLocation')->andReturn($location);
        $ship->shouldReceive('isWarped', 'isCloaked')->andReturn(false);
        $ship->shouldReceive('hasEnoughCrew')->andReturn(true);
        if ($moves) {
            $ship->shouldNotReceive('canIntercept');
        } else {
            $ship->shouldReceive('canIntercept')->once()->andReturn(false);
        }
        $wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($this->mock(ShipWrapperInterface::class));
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $battle = new BorgSphereBattle($attacks, $wrappers, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(FightLibInterface::class), new BorgSphereAttackContext());
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        new BorgSphereBehavior($navigation, $battle, $random, $this->interception($battle, $wrappers))->work($event, $ship);
        self::assertSame($moves ? BorgSpherePhaseEnum::ROAMING : $phase, $event->getPhase());
        self::assertFalse($event->isInitialAttackDone());
    }

    #[DataProvider('phases')]
    public function testAnActiveAttackKeepsTheSphereAtItsCurrentPosition(BorgSpherePhaseEnum $phase): void
    {
        $location = new StarSystemMap();
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $ships = [];
        $shipWrappers = [];
        foreach ([19, 261] as $id) {
            $ship = $this->ship($location, $id, $id);
            $ship->shouldReceive('isWarped')->andReturn(false);
            $ship->shouldNotReceive('canIntercept');
            $wrapper = $this->mock(ShipWrapperInterface::class);
            $wrapper->shouldReceive('canFire')->andReturn(true);
            $wrapper->shouldReceive('get')->andReturn($ship);
            $wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($wrapper);
            $ships[] = $ship;
            $shipWrappers[] = $wrapper;
        }
        $location->getSpacecrafts()->add($ships[1]);
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldReceive('attack')->once()->with($shipWrappers[0], $shipWrappers[1], false, false, InformationWrapper::class)->andReturn(true);
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->once()->andReturn(false);
        $fight = $this->mock(FightLibInterface::class);
        $fight->shouldReceive('canAttackTarget')->once()->andReturn(true);
        $battle = new BorgSphereBattle($attacks, $wrappers, $relations, $fight, new BorgSphereAttackContext());
        $event = new BorgSphereEvent()->setPhase($phase)->setConfiguration(new BorgSphereConfiguration());

        $this->behavior($battle, $wrappers)->work($event, $ships[0]);

        self::assertSame($location, $ships[0]->getLocation());
        self::assertSame($phase, $event->getPhase());
    }

    /** @return iterable<string, array{BorgSpherePhaseEnum, bool, bool}> */
    public static function inactiveSpheres(): iterable
    {
        yield 'disabled' => [BorgSpherePhaseEnum::ROAMING, true, false];
        yield 'regenerating' => [BorgSpherePhaseEnum::REGENERATING, false, false];
        yield 'destroyed' => [BorgSpherePhaseEnum::ROAMING, false, true];
        yield 'escaped' => [BorgSpherePhaseEnum::ESCAPED, false, false];
    }

    #[DataProvider('inactiveSpheres')]
    public function testInactiveSphereNeitherChecksAnomaliesNorAttacksInterceptsOrMoves(BorgSpherePhaseEnum $phase, bool $disabled, bool $destroyed): void
    {
        $ship = $this->mock(Ship::class);
        $ship->shouldReceive('getCondition')->andReturn(new SpacecraftCondition($ship)->setDisabled($disabled)->setIsDestroyed($destroyed));
        $ship->shouldNotReceive('getLocation', 'canIntercept');
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldNotReceive('wrapShip');
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $battle = new BorgSphereBattle($attacks, $wrappers, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(FightLibInterface::class), new BorgSphereAttackContext());
        $event = new BorgSphereEvent()->setPhase($phase);

        $this->behavior($battle, $wrappers)->work($event, $ship);

        self::assertSame($phase, $event->getPhase());
    }

    /** @return iterable<string, array{bool}> */
    public static function anomalyEscapeRoutes(): iterable
    {
        yield 'clear neighboring field' => [true];
        yield 'no escape route' => [false];
    }

    #[DataProvider('anomalyEscapeRoutes')]
    public function testAnomalyEscapeTakesPriorityOverLocalEnemiesAndTheIdleRoll(bool $escapeRoute): void
    {
        $system = $this->mock(StarSystem::class);
        $system->shouldReceive('getId')->andReturn(1);
        $system->shouldReceive('getMap')->andReturn(null);
        $location = new StarSystemMap()->setSx(2)->setSy(2)->setSystem($system);
        $destination = new StarSystemMap();
        $destination->setSx(3)->setSy(2)->setSystem($system)->setFieldType(new MapFieldType()->setPassable(true));
        $anomaly = $this->mock(Anomaly::class);
        $anomaly->shouldReceive('isActive')->andReturn(true);
        $location->getAnomalies()->add($anomaly);
        $enemy = $this->ship($location, 2, 261);
        $enemy->shouldReceive('isWarped')->andReturn(false);
        $location->getSpacecrafts()->add($enemy);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(19);
        $ship = new Ship();
        $ship->setUser($user)->setLocation($location)->setCondition(new SpacecraftCondition($ship));
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldNotReceive('canFire');
        $mover = $this->mock(ShipMoverInterface::class);
        $routes = $this->mock(FlightRouteFactoryInterface::class);
        $messages = $this->mock(DistributedMessageSenderInterface::class);
        if ($escapeRoute) {
            $wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($wrapper);
            $route = $this->mock(FlightRouteInterface::class);
            $routes->shouldReceive('getRouteForCoordinateDestination')->once()->with($ship, 3, 2)->andReturn($route);
            $route->shouldReceive('hasSpecialDamageOnField')->once()->andReturn(false);
            $collection = $this->mock(MessageCollectionInterface::class);
            $mover->shouldReceive('checkAndMove')->once()->with($wrapper, $route)->andReturnUsing(static function () use ($ship, $destination, $collection): MessageCollectionInterface {
                $ship->setLocation($destination);
                return $collection;
            });
            $messages->shouldReceive('distributeMessageCollection')->once();
        } else {
            $wrappers->shouldNotReceive('wrapShip');
            $mover->shouldNotReceive('checkAndMove');
        }
        $flight = new BorgSphereFlight($mover, $routes, $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $messages);
        $systemMaps = $this->mock(StarSystemMapRepositoryInterface::class);
        $systemMaps->shouldReceive('getByCoordinates')->times(4)->andReturnUsing(static fn (int $systemId, int $x, int $y): ?StarSystemMap => $escapeRoute && $x === 3 && $y === 2 ? $destination : null);
        $navigationRandom = $this->mock(StuRandom::class);
        $navigationRandom->shouldReceive('rand')->once()->with(1, 7)->andReturn(7);
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $systemMaps, $wrappers, $flight, $navigationRandom);
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $battle = new BorgSphereBattle($attacks, $wrappers, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(FightLibInterface::class), new BorgSphereAttackContext());
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        $event = new BorgSphereEvent()->setPhase(BorgSpherePhaseEnum::ROAMING)->setConfiguration(new BorgSphereConfiguration()->setSettings(['movement_min_fields' => 1, 'movement_max_fields' => 7, 'idle_chance_percent' => 20]));

        new BorgSphereBehavior($navigation, $battle, $random, $this->interception($battle, $wrappers))->work($event, $ship);

        self::assertSame($escapeRoute ? $destination : $location, $ship->getLocation());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
    }

    /** @return iterable<string, array{int, bool}> */
    public static function interceptionRolls(): iterable
    {
        yield 'last successful roll' => [40, true];
        yield 'first unsuccessful roll' => [41, false];
    }

    #[DataProvider('interceptionRolls')]
    public function testIdleSphereMayInterceptAndFightOnceWithoutMoving(int $roll, bool $intercepted): void
    {
        $location = new StarSystemMap();
        $ship = $this->ship($location, 1, 19);
        $ship->shouldReceive('isWarped')->andReturn(false);
        $ship->shouldReceive('canIntercept')->once()->andReturn(true);
        $target = $this->ship($location, 2, 261);
        $warped = true;
        $target->shouldReceive('isWarped')->andReturnUsing(static function () use (&$warped): bool {
            return $warped;
        });
        $other = $this->ship($location, 3, 262);
        $other->shouldReceive('isWarped')->andReturn(true);
        $location->getSpacecrafts()->add($target);
        $location->getSpacecrafts()->add($other);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $sourceWrapper = $this->mock(ShipWrapperInterface::class);
        $sourceWrapper->shouldReceive('get')->andReturn($ship);
        $sourceWrapper->shouldReceive('canFire')->andReturn(true);
        $wrappers->shouldReceive('wrapShip')->with($ship)->times($intercepted ? 3 : 2)->andReturn($sourceWrapper);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $sequence = [];
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $intercept = $this->mock(InterceptShipCoreInterface::class);
        $fight = $this->mock(FightLibInterface::class);
        if ($intercepted) {
            $wrappers->shouldReceive('wrapShip')->with($target)->twice()->andReturn($targetWrapper);
            $intercept->shouldReceive('intercept')->once()->with($sourceWrapper, $targetWrapper, InformationWrapper::class, false, true)->andReturnUsing(static function () use (&$warped, &$sequence): void {
                $warped = false;
                $sequence[] = 'intercept';
            });
            $fight->shouldReceive('canAttackTarget')->once()->with($ship, $target, true)->andReturn(true);
            $attacks->shouldReceive('attack')->once()->with($sourceWrapper, $targetWrapper, false, false, InformationWrapper::class)->andReturnUsing(static function () use (&$sequence): bool {
                $sequence[] = 'attack';
                return true;
            });
        } else {
            $intercept->shouldNotReceive('intercept');
            $attacks->shouldNotReceive('attack');
        }
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->times($intercepted ? 3 : 2)->andReturn(false);
        $context = new BorgSphereAttackContext();
        $battle = new BorgSphereBattle($attacks, $wrappers, $relations, $fight, $context);
        $interceptionRandom = $this->mock(StuRandom::class);
        $interceptionRandom->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        if ($intercepted) {
            $interceptionRandom->shouldReceive('array_rand')->once()->andReturn('ship:2');
        } else {
            $interceptionRandom->shouldNotReceive('array_rand');
        }
        $interception = new BorgSphereInterception($intercept, $wrappers, $relations, $battle, $context, $interceptionRandom);
        $mover = $this->mock(ShipMoverInterface::class);
        $mover->shouldNotReceive('checkAndMove');
        $navigationRandom = $this->mock(StuRandom::class);
        $navigationRandom->shouldNotReceive('rand');
        $flight = new BorgSphereFlight($mover, $this->mock(FlightRouteFactoryInterface::class), $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $this->mock(DistributedMessageSenderInterface::class));
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $this->mock(StarSystemMapRepositoryInterface::class), $wrappers, $flight, $navigationRandom);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(20);
        $event = new BorgSphereEvent()->setPhase(BorgSpherePhaseEnum::ROAMING)->setConfiguration(new BorgSphereConfiguration()->setSettings(['idle_chance_percent' => 20, 'intercept_chance_percent' => 40]));

        new BorgSphereBehavior($navigation, $battle, $random, $interception)->work($event, $ship);

        self::assertSame($intercepted ? ['intercept', 'attack'] : [], $sequence);
        self::assertSame($location, $ship->getLocation());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
    }

    private function ship(StarSystemMap $location, int $id, int $userId): Ship&MockInterface
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($userId);
        $user->shouldReceive('isVacationRequestOldEnough')->andReturn(false);
        $ship = $this->mock(Ship::class);
        $ship->shouldReceive('getId')->andReturn($id);
        $ship->shouldReceive('getUser')->andReturn($user);
        $ship->shouldReceive('getLocation')->andReturn($location);
        $ship->shouldReceive('getCondition')->andReturn(new SpacecraftCondition($ship));
        $ship->shouldReceive('isCloaked', 'isRpgModuleInvisible')->andReturn(false);
        $ship->shouldReceive('hasEnoughCrew')->andReturn(true);
        $ship->shouldReceive('getFleetId')->andReturn(null);
        $ship->shouldReceive('getRump')->andReturn(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
        return $ship;
    }

    private function behavior(BorgSphereBattle $battle, SpacecraftWrapperFactoryInterface $wrappers): BorgSphereBehavior
    {
        $flight = new BorgSphereFlight($this->mock(ShipMoverInterface::class), $this->mock(FlightRouteFactoryInterface::class), $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $this->mock(DistributedMessageSenderInterface::class));
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $this->mock(StarSystemMapRepositoryInterface::class), $wrappers, $flight, $this->mock(StuRandom::class));
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        return new BorgSphereBehavior($navigation, $battle, $random, $this->interception($battle, $wrappers));
    }

    private function interception(BorgSphereBattle $battle, SpacecraftWrapperFactoryInterface $wrappers): BorgSphereInterception
    {
        return new BorgSphereInterception(
            $this->mock(InterceptShipCoreInterface::class),
            $wrappers,
            $this->mock(PlayerRelationDeterminatorInterface::class),
            $battle,
            new BorgSphereAttackContext(),
            $this->mock(StuRandom::class)
        );
    }
}
