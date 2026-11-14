<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

final class BorgCubeCaptureSchedulingTest extends StuTestCase
{
    /** @return iterable<string, array{BorgSpherePhaseEnum}> */
    public static function movingPhases(): iterable
    {
        yield 'approach' => [BorgSpherePhaseEnum::APPROACH];
        yield 'roaming' => [BorgSpherePhaseEnum::ROAMING];
    }

    #[DataProvider('movingPhases')]
    public function testCaptureIsRolledOnlyOnceAtTheDestinationInsteadOfOnEveryIntermediateField(BorgSpherePhaseEnum $phase): void
    {
        [$behavior, $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights] = $this->fixture();
        $middle = $this->ship(2, 261, $fields[1]);
        $target = $this->ship(3, 262, $fields[3]);
        $event->setPhase($phase);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(33);
        $random->shouldReceive('array_rand')->once()->with([$target])->andReturn(0);
        $repository->shouldReceive('lock')->once()->with($cube);
        $repository->shouldReceive('lock')->once()->with($target);
        $repository->shouldReceive('save')->once();
        $fleets->shouldReceive('leaveFleet')->once()->with($target)->andReturn(false);
        $messages->shouldReceive('send')->once();

        $behavior->work($event, $cube);

        self::assertCount(3, $flights);
        self::assertSame($fields[3], $cube->getLocation());
        self::assertSame($target, $cube->getTractoredShip());
        self::assertFalse($middle->isBeingAssimilated());
        self::assertTrue($target->isBeingAssimilated());
    }

    public function testFailedStartRollCannotBeRepeatedAfterMovingToAnotherTarget(): void
    {
        [$behavior, $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights] = $this->fixture();
        $first = $this->ship(2, 261, $fields[0]);
        $last = $this->ship(3, 262, $fields[3]);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(34);
        $random->shouldNotReceive('array_rand');
        $repository->shouldReceive('lock')->once()->with($cube);
        $repository->shouldNotReceive('save');
        $fleets->shouldNotReceive('leaveFleet');
        $messages->shouldNotReceive('send');

        $behavior->work($event, $cube);

        self::assertCount(3, $flights);
        self::assertSame($fields[3], $cube->getLocation());
        self::assertNull($cube->getTractoredShip());
        self::assertFalse($first->isBeingAssimilated());
        self::assertFalse($last->isBeingAssimilated());
    }

    public function testBlockedMovementDoesNotCauseAnAssimilationAttempt(): void
    {
        [$behavior, $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights] = $this->fixture(blocked: true);
        $this->ship(2, 261, $fields[1]);
        $random->shouldNotReceive('rand', 'array_rand');
        $repository->shouldNotReceive('lock', 'save');
        $fleets->shouldNotReceive('leaveFleet');
        $messages->shouldNotReceive('send');

        $behavior->work($event, $cube);

        self::assertCount(1, $flights);
        self::assertSame($fields[0], $cube->getLocation());
    }

    public function testStandingStillWithoutLocalTargetsDoesNotSpendAnAssimilationRoll(): void
    {
        [$behavior, $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights] = $this->fixture(idle: true);
        $this->ship(2, 261, $fields[1]);
        $random->shouldNotReceive('rand', 'array_rand');
        $repository->shouldNotReceive('lock', 'save');
        $fleets->shouldNotReceive('leaveFleet');
        $messages->shouldNotReceive('send');

        $behavior->work($event, $cube);

        self::assertCount(0, $flights);
        self::assertSame($fields[0], $cube->getLocation());
    }

    public function testAnomalyEscapeKeepsPriorityWithoutAssimilatingOnItsDestination(): void
    {
        [$behavior, $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights] = $this->fixture(anomaly: true);
        $type = $this->mock(AnomalyType::class);
        $type->shouldReceive('getId')->andReturn(AnomalyTypeEnum::ION_STORM->value);
        new Anomaly()->setAnomalyType($type)->setRemainingTicks(1)->setLocation($fields[0]);
        $target = $this->ship(2, 261, $fields[1]);
        $random->shouldNotReceive('rand', 'array_rand');
        $repository->shouldNotReceive('lock', 'save');
        $fleets->shouldNotReceive('leaveFleet');
        $messages->shouldNotReceive('send');

        $behavior->work($event, $cube);

        self::assertCount(1, $flights);
        self::assertSame($fields[1], $cube->getLocation());
        self::assertFalse($target->isBeingAssimilated());
    }

    public function testActiveCapturePreventsMovementAndAnotherAssimilationRoll(): void
    {
        [$behavior, $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights] = $this->fixture(activeCapture: true);
        $target = $cube->getTractoredShip();
        $random->shouldNotReceive('rand', 'array_rand');
        $repository->shouldNotReceive('lock', 'save');
        $fleets->shouldNotReceive('leaveFleet');
        $messages->shouldNotReceive('send');

        $behavior->work($event, $cube);

        self::assertCount(0, $flights);
        self::assertSame($fields[0], $cube->getLocation());
        self::assertSame($target, $cube->getTractoredShip());
        self::assertTrue($target?->isBeingAssimilated());
    }

    /** @return array{BorgSphereBehavior, Ship, BorgSphereEvent, list<StarSystemMap>, StuRandom&MockInterface, BorgAssimilationRepositoryInterface&MockInterface, LeaveFleetInterface&MockInterface, PrivateMessageSenderInterface&MockInterface, ArrayCollection<int, StarSystemMap>} */
    private function fixture(bool $blocked = false, bool $idle = false, bool $anomaly = false, bool $activeCapture = false): array
    {
        $system = $this->mock(StarSystem::class);
        $system->shouldReceive('getId')->andReturn(1);
        $system->shouldReceive('getMaxX', 'getMaxY')->andReturn(30);
        $system->shouldReceive('getMap')->andReturnNull();
        $system->shouldReceive('getName')->andReturn('Test');
        $system->shouldReceive('isWormhole')->andReturn(false);
        $fields = [];
        foreach (range(2, 5) as $x) {
            $field = new StarSystemMap()->setSystem($system)->setSx($x)->setSy(2);
            $field->setFieldType(new MapFieldType()->setPassable(true));
            $fields[] = $field;
        }
        $cube = $this->ship(1, 19, $fields[0]);
        $cube->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, new SpacecraftSystem()->setSpacecraft($cube)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_OFF));
        $event = new BorgSphereEvent()->setShipId(1)->setPhase(BorgSpherePhaseEnum::ROAMING)->setConfiguration(new BorgSphereConfiguration()->setColony(new Colony()->setStarsystemMap($fields[3]))->setSettings([
            'assimilation_enabled' => 1, 'assimilation_chance_percent' => 33, 'assimilation_duration_seconds' => 900, 'nanite_commodity_id' => 76,
            'idle_chance_percent' => 20, 'movement_min_fields' => 1, 'movement_max_fields' => 7, 'roam_radius' => 20
        ]));
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturn(strtotime('2026-11-14 12:00:00'));
        $random = $this->mock(StuRandom::class);
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $entries = [];
        if ($activeCapture) {
            $target = $this->ship(2, 261, $fields[0]);
            $cube->setTractoredShip($target);
            $cube->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $entries[] = new SpacecraftAssimilated()->setSpacecraft($target)->start($cube, $clock->time(), 900);
        }
        $repository->shouldReceive('getBySource')->with($cube)->andReturn($entries);
        $repository->shouldReceive('isAssimilateable')->andReturn(true);
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $fleets = $this->mock(LeaveFleetInterface::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $capture = new BorgAssimilationCapture($repository, $relations, $fleets, $messages, $random, $clock);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $cubeWrapper = $this->mock(ShipWrapperInterface::class);
        $cubeWrapper->shouldReceive('get')->andReturn($cube);
        $cubeWrapper->shouldReceive('canFire')->andReturn(false);
        $wrappers->shouldReceive('wrapShip')->with($cube)->andReturn($cubeWrapper);
        $torpedoes = new BorgSphereTorpedoSupply($wrappers, $this->mock(ShipTorpedoManagerInterface::class), $this->mock(TorpedoTypeRepositoryInterface::class), $this->mock(TorpedoStorageRepositoryInterface::class), $random);
        $completion = new BorgAssimilationCompletion($repository, $this->mock(FleetRepositoryInterface::class), $this->mock(StorageRepositoryInterface::class), $this->mock(EntryCreatorInterface::class), $messages, new BorgSphereEnergy($wrappers), $torpedoes);
        $release = new BorgAssimilationRelease($repository, $this->mock(CommodityRepositoryInterface::class), $this->mock(StorageManagerInterface::class), $messages, $random, $this->mock(BorgSphereRepositoryInterface::class));
        $assimilation = new BorgAssimilationService($repository, $capture, $completion, $release, $clock);
        $navigationRandom = $this->mock(StuRandom::class);
        if (!$idle && !$activeCapture) {
            $navigationRandom->shouldReceive('rand')->once()->with(1, 7)->andReturn(3);
        }
        $navigationRandom->shouldReceive('array_rand')->andReturn(0);
        $maps = $this->mock(StarSystemMapRepositoryInterface::class);
        $maps->shouldReceive('getByCoordinates')->andReturnUsing(static fn (int $systemId, int $x, int $y): ?StarSystemMap => array_find($fields, static fn (StarSystemMap $field): bool => $field->getX() === $x && $field->getY() === $y));
        $route = $this->mock(FlightRouteInterface::class);
        $route->shouldReceive('hasSpecialDamageOnField')->andReturn(false);
        $destination = null;
        $routes = $this->mock(FlightRouteFactoryInterface::class);
        $routes->shouldReceive('getRouteForCoordinateDestination')->andReturnUsing(static function (Ship $ship, int $x, int $y) use ($fields, $route, &$destination): FlightRouteInterface {
            $destination = array_find($fields, static fn (StarSystemMap $field): bool => $field->getX() === $x && $field->getY() === $y);
            return $route;
        });
        $flights = new ArrayCollection();
        $mover = $this->mock(ShipMoverInterface::class);
        $mover->shouldReceive('checkAndMove')->andReturnUsing(function () use (&$destination, $cube, $blocked, $flights): MessageCollectionInterface {
            self::assertNotNull($destination);
            $flights->add($destination);
            if (!$blocked) {
                $cube->setLocation($destination);
            }
            return $this->mock(MessageCollectionInterface::class);
        });
        $distributed = $this->mock(DistributedMessageSenderInterface::class);
        $distributed->shouldReceive('distributeMessageCollection');
        $flight = new BorgSphereFlight($mover, $routes, $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $distributed);
        $activity = new BorgSphereActivityPolicy($clock);
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $maps, $wrappers, $flight, $navigationRandom, $activity);
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $context = new BorgSphereAttackContext();
        $battle = new BorgSphereBattle($attacks, $wrappers, $relations, $this->mock(FightLibInterface::class), $context);
        $interception = new BorgSphereInterception($this->mock(InterceptShipCoreInterface::class), $wrappers, $relations, $battle, $context, $this->mock(StuRandom::class), $activity);
        $behaviorRandom = $this->mock(StuRandom::class);
        if (!$anomaly && !$activeCapture) {
            $behaviorRandom->shouldReceive('rand')->once()->with(1, 100)->andReturn($idle ? 20 : 100);
        }
        return [new BorgSphereBehavior($navigation, $battle, $behaviorRandom, $interception, $assimilation), $cube, $event, $fields, $random, $repository, $fleets, $messages, $flights];
    }

    private function ship(int $id, int $userId, StarSystemMap $field): Ship
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, $userId);
        $ship = new Ship()->setUser($user)->setLocation($field)->setName('Schiff');
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setCondition(new SpacecraftCondition($ship))->setBuildplan(new SpacecraftBuildplan()->setCrew(0))->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::CRUISER));
        $field->getSpacecrafts()->add($ship);
        return $ship;
    }
}
