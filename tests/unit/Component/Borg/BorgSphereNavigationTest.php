<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereNavigationTest extends StuTestCase
{
    /** @return iterable<string, array{int, int, int, bool}> */
    public static function radiusPositions(): iterable
    {
        yield 'east boundary' => [20, 0, 20, true];
        yield 'circular boundary' => [12, 16, 20, true];
        yield 'negative circular boundary' => [-12, -16, 20, true];
        yield 'diagonal outside circle' => [15, 15, 20, false];
        yield 'one field outside edge' => [20, 1, 20, false];
        yield 'beyond boundary' => [21, 0, 20, false];
        yield 'only origin at zero radius' => [0, 0, 0, true];
        yield 'negative radius' => [0, 0, -1, false];
    }

    #[DataProvider('radiusPositions')]
    public function testRoamingUsesCircularDistanceFromTheAnchoredSystem(int $dx, int $dy, int $radius, bool $expected): void
    {
        self::assertSame($expected, BorgSphereNavigation::isWithinRadius($dx, $dy, $radius));
    }

    /** @return iterable<string, array{bool, int, int, bool}> */
    public static function fieldSafety(): iterable
    {
        yield 'clear space' => [true, 0, 0, true];
        yield 'ordinary space with deflector damage' => [true, 6, 0, true];
        yield 'class K colony orbit with deflector damage' => [true, 10, 0, true];
        yield 'passable field with special damage' => [true, 0, 1, false];
        yield 'passable sun with existing solar damage convention' => [true, 100, 7500, false];
        yield 'impassable field' => [false, 0, 0, false];
    }

    #[DataProvider('fieldSafety')]
    public function testSafeFieldsUseDamageMetadataForMapAndSystemLocations(bool $passable, int $damage, int $specialDamage, bool $expected): void
    {
        $fieldType = new MapFieldType()->setPassable($passable)->setDamage($damage)->setSpecialDamage($specialDamage);
        $navigation = $this->navigation();
        foreach ([new Map(), new StarSystemMap()] as $location) {
            $location->setFieldType($fieldType);
            self::assertSame($expected, $navigation->isSafe($location));
        }
    }

    /** @return iterable<string, array{SpacecraftAlertStateEnum, bool}> */
    public static function alertStates(): iterable
    {
        yield 'red alert remains forbidden' => [SpacecraftAlertStateEnum::ALERT_RED, false];
        yield 'yellow alert on normal space is safe' => [SpacecraftAlertStateEnum::ALERT_YELLOW, true];
    }

    #[DataProvider('alertStates')]
    public function testAlertRedExclusionRemainsEffectiveOnSafeTerrain(SpacecraftAlertStateEnum $alert, bool $expected): void
    {
        $location = new Map();
        $location->setFieldType(new MapFieldType()->setPassable(true)->setDamage(6));
        $ship = $this->mock(Ship::class);
        $ship->shouldReceive('hasComputer')->once()->andReturn(true);
        $location->getSpacecrafts()->add($ship);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('getAlertState')->once()->andReturn($alert);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldReceive('wrapSpacecraft')->with($ship)->once()->andReturn($wrapper);
        self::assertSame($expected, $this->navigation(wrappers: $wrappers)->isSafe($location));
    }

    public function testSpawnSkipsADistantSunButStillUsesOrdinarySpace(): void
    {
        $target = new StarSystemMap()->setSx(1)->setSy(1);
        $safe = new StarSystemMap()->setSx(3)->setSy(3);
        $safe->setFieldType(new MapFieldType()->setPassable(true)->setDamage(6));
        $sun = new StarSystemMap()->setSx(20)->setSy(20);
        $sun->setFieldType(new MapFieldType()->setPassable(true)->setDamage(100)->setSpecialDamage(7500));
        $configuration = new BorgSphereConfiguration()->setColony(new Colony()->setStarsystemMap($target));
        $systemMaps = $this->mock(StarSystemMapRepositoryInterface::class);
        $systemMaps->shouldReceive('getBySystemOrdered')->with(0)->once()->andReturn([$target, $sun, $safe]);
        self::assertSame($safe, $this->navigation($systemMaps)->spawnLocation($configuration));
    }

    /** @return iterable<string, array{int, bool}> */
    public static function movementBudgets(): iterable
    {
        foreach ([1, 7] as $budget) {
            yield 'approach ' . $budget => [$budget, true];
            yield 'roam ' . $budget => [$budget, false];
        }
    }

    #[DataProvider('movementBudgets')]
    public function testMovementUsesTheSingleRolledBudgetWithoutRevisitingFields(int $budget, bool $approach): void
    {
        $fields = $this->systemRow(10);
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[9], $fields, [], $budget);

        $approach ? $navigation->approach($ship, $configuration) : $navigation->roam($ship, $configuration);

        self::assertCount($budget, $flights);
        self::assertSame(array_slice($fields, 1, $budget), $flights->toArray());
        self::assertSame($fields[$budget], $ship->getLocation());
    }

    public function testApproachStopsAtTheColonyWithoutUsingTheRestOfItsBudget(): void
    {
        $fields = $this->systemRow(4);
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[2], $fields, [], 7);

        $navigation->approach($ship, $configuration);

        self::assertCount(2, $flights);
        self::assertSame($fields[2], $ship->getLocation());
    }

    public function testApproachAlreadyAtTheColonyDoesNotLeaveIt(): void
    {
        $fields = $this->systemRow(2);
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[0], $fields, [], 7);

        $navigation->approach($ship, $configuration);

        self::assertCount(0, $flights);
        self::assertSame($fields[0], $ship->getLocation());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function obstacles(): iterable
    {
        foreach (['sun', 'red alert', 'impassable', 'anomaly'] as $obstacle) {
            yield $obstacle . ' during approach' => [$obstacle, true];
            yield $obstacle . ' during roaming' => [$obstacle, false];
        }
    }

    #[DataProvider('obstacles')]
    public function testEachIntermediateFieldIsCheckedBeforeFlying(string $obstacle, bool $approach): void
    {
        $fields = $this->systemRow(4);
        if ($obstacle === 'sun') {
            $fields[2]->setFieldType(new MapFieldType()->setPassable(true)->setSpecialDamage(7500));
        } elseif ($obstacle === 'impassable') {
            $fields[2]->setFieldType(new MapFieldType()->setPassable(false));
        } elseif ($obstacle === 'anomaly') {
            $this->anomaly($fields[2], AnomalyTypeEnum::ION_STORM);
        } else {
            $hostile = $this->mock(Ship::class);
            $hostile->shouldReceive('hasComputer')->andReturn(true);
            $fields[2]->getSpacecrafts()->add($hostile);
        }
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[3], $fields, [], 7);

        $approach ? $navigation->approach($ship, $configuration) : $navigation->roam($ship, $configuration);

        self::assertSame([$fields[1]], $flights->toArray());
        self::assertSame($fields[1], $ship->getLocation());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function interruptedFlights(): iterable
    {
        foreach (['disabled', 'destroyed', 'blocked'] as $outcome) {
            yield $outcome . ' during approach' => [$outcome, true];
            yield $outcome . ' during roaming' => [$outcome, false];
        }
    }

    #[DataProvider('interruptedFlights')]
    public function testInterruptedMovementDoesNotSpendRemainingBudget(string $outcome, bool $approach): void
    {
        $fields = $this->systemRow(10);
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[9], $fields, [], 7, static function (Ship $ship, Location $destination) use ($outcome): void {
            if ($outcome !== 'blocked') {
                $ship->setLocation($destination);
            }
            if ($outcome === 'disabled') {
                $ship->getCondition()->setDisabled(true);
            } elseif ($outcome === 'destroyed') {
                $ship->getCondition()->setIsDestroyed(true);
            }
        });

        $approach ? $navigation->approach($ship, $configuration) : $navigation->roam($ship, $configuration);

        self::assertCount(1, $flights);
        self::assertSame($fields[$outcome === 'blocked' ? 0 : 1], $ship->getLocation());
    }

    public function testRoamingKeepsEveryStepWithinTheConfiguredOuterRadius(): void
    {
        $anchor = $this->mapField(0);
        $fields = $this->systemRow(1, $anchor);
        $maps = [$this->mapField(19), $this->mapField(20), $this->mapField(21)];
        [$navigation, $ship, $configuration, $flights] = $this->movement($maps[0], $fields[0], $fields, $maps, 7);

        $navigation->roam($ship, $configuration);

        self::assertSame([$maps[1]], $flights->toArray());
        self::assertSame($maps[1], $ship->getLocation());
    }

    /** @return iterable<string, array{int, bool}> */
    public static function systemTransitions(): iterable
    {
        foreach ([1, 7] as $budget) {
            yield 'exit with budget ' . $budget => [$budget, true];
            yield 'entry with budget ' . $budget => [$budget, false];
        }
    }

    #[DataProvider('systemTransitions')]
    public function testSystemTransitionConsumesOneStepOfTheSameBudget(int $budget, bool $exit): void
    {
        $anchor = $this->mapField(0);
        $fields = $this->systemRow($exit ? 1 : 10, $anchor);
        $anchor->setSystem($fields[0]->getSystem());
        $maps = [$anchor];
        if ($exit) {
            foreach (range(1, 8) as $x) {
                $maps[] = $this->mapField($x);
            }
        }
        $source = $exit ? $fields[0] : $anchor;
        [$navigation, $ship, $configuration, $flights] = $this->movement($source, $fields[0], $fields, $maps, $budget);

        $navigation->roam($ship, $configuration);

        self::assertCount($budget, $flights);
        self::assertSame($exit ? $maps[$budget - 1] : $fields[$budget - 1], $ship->getLocation());
        self::assertCount($budget, array_unique(array_map(spl_object_id(...), $flights->toArray())));
    }

    /** @return iterable<string, array{AnomalyTypeEnum}> */
    public static function anomalyKinds(): iterable
    {
        yield 'subspace ellipse' => [AnomalyTypeEnum::SUBSPACE_ELLIPSE];
        yield 'ion storm' => [AnomalyTypeEnum::ION_STORM];
    }

    #[DataProvider('anomalyKinds')]
    public function testNormalDestinationsAvoidActiveAnomaliesOnBothMaps(AnomalyTypeEnum $type): void
    {
        $navigation = $this->navigation();
        foreach ([new Map(), new StarSystemMap()] as $location) {
            $location->setFieldType(new MapFieldType()->setPassable(true));
            $this->anomaly($location, $type);
            self::assertFalse($navigation->isSafe($location));
            $anomaly = $location->getAnomaly($type);
            self::assertNotNull($anomaly);
            $anomaly->setRemainingTicks(0);
            self::assertTrue($navigation->isSafe($location));
        }
    }

    public function testSpawnRejectsAnActiveAnomalyEvenWhenItIsFarthestAway(): void
    {
        $fields = $this->systemRow(4);
        $this->anomaly($fields[3], AnomalyTypeEnum::ION_STORM);
        $configuration = new BorgSphereConfiguration()->setColony(new Colony()->setStarsystemMap($fields[0]));
        $maps = $this->mock(StarSystemMapRepositoryInterface::class);
        $maps->shouldReceive('getBySystemOrdered')->once()->with(0)->andReturn($fields);

        self::assertSame($fields[2], $this->navigation($maps)->spawnLocation($configuration));
    }

    public function testNoActiveAnomalyDoesNotConsumeABudgetOrAttemptFlight(): void
    {
        $location = new StarSystemMap();
        $this->anomaly($location, AnomalyTypeEnum::ION_STORM, 0);
        $ship = new Ship();
        $ship->setLocation($location);

        self::assertFalse($this->navigation()->evadeAnomaly($ship, new BorgSphereConfiguration()));
        self::assertSame($location, $ship->getLocation());
    }

    #[DataProvider('anomalyKinds')]
    public function testEscapeUsesTheNearestClearFieldAndStopsThere(AnomalyTypeEnum $type): void
    {
        $fields = $this->systemRow(4);
        $this->anomaly($fields[1], $type);
        $this->anomaly($fields[2], $type);
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[1], $fields[3], $fields, [], 7);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertSame([$fields[0]], $flights->toArray());
        self::assertSame($fields[0], $ship->getLocation());
    }

    #[DataProvider('anomalyKinds')]
    public function testEscapeCrossesAnAnomalyClusterWithOrdinaryNeighborFlights(AnomalyTypeEnum $type): void
    {
        $fields = $this->systemRow(5);
        foreach (array_slice($fields, 0, 3) as $field) {
            $this->anomaly($field, $type);
        }
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[4], $fields, [], 7);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertSame(array_slice($fields, 1, 3), $flights->toArray());
        self::assertSame($fields[3], $ship->getLocation());
    }

    public function testEscapeDoesNotSearchOrFlyBeyondTheRolledBudget(): void
    {
        $fields = $this->systemRow(4);
        foreach (array_slice($fields, 0, 3) as $field) {
            $this->anomaly($field, AnomalyTypeEnum::ION_STORM);
        }
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[3], $fields, [], 2);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertCount(0, $flights);
        self::assertSame($fields[0], $ship->getLocation());
    }

    /** @return iterable<string, array{string}> */
    public static function unsafeEscapeFields(): iterable
    {
        yield 'sun' => ['sun'];
        yield 'red alert' => ['red alert'];
        yield 'impassable' => ['impassable'];
    }

    #[DataProvider('unsafeEscapeFields')]
    public function testEscapeCannotUseOtherwiseUnsafeIntermediateFields(string $obstacle): void
    {
        $fields = $this->systemRow(3);
        $this->anomaly($fields[0], AnomalyTypeEnum::ION_STORM);
        $this->anomaly($fields[1], AnomalyTypeEnum::ION_STORM);
        if ($obstacle === 'sun') {
            $fields[1]->setFieldType(new MapFieldType()->setPassable(true)->setSpecialDamage(7500));
        } elseif ($obstacle === 'impassable') {
            $fields[1]->setFieldType(new MapFieldType()->setPassable(false));
        } else {
            $hostile = $this->mock(Ship::class);
            $hostile->shouldReceive('hasComputer')->andReturn(true);
            $fields[1]->getSpacecrafts()->add($hostile);
        }
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[2], $fields, [], 7);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertCount(0, $flights);
        self::assertSame($fields[0], $ship->getLocation());
    }

    public function testEscapeRemainsWithinTheOuterRadiusEvenWhenTheOutsideNeighborIsClear(): void
    {
        $anchor = $this->mapField(0);
        $fields = $this->systemRow(1, $anchor);
        $maps = [$this->mapField(18), $this->mapField(19), $this->mapField(20), $this->mapField(21)];
        $this->anomaly($maps[1], AnomalyTypeEnum::ION_STORM);
        $this->anomaly($maps[2], AnomalyTypeEnum::ION_STORM);
        [$navigation, $ship, $configuration, $flights] = $this->movement($maps[2], $fields[0], $fields, $maps, 7);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertSame([$maps[1], $maps[0]], $flights->toArray());
        self::assertSame($maps[0], $ship->getLocation());
    }

    public function testEscapeCannotLeaveASystemOntoAnOuterFieldBeyondTheRadius(): void
    {
        $anchor = $this->mapField(0);
        $outside = $this->mapField(21);
        $target = $this->systemRow(1, $anchor)[0];
        $source = $this->systemRow(1, $outside)[0];
        $this->anomaly($source, AnomalyTypeEnum::ION_STORM);
        [$navigation, $ship, $configuration, $flights] = $this->movement($source, $target, [$source], [$anchor, $outside], 7);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertCount(0, $flights);
        self::assertSame($source, $ship->getLocation());
    }

    /** @return iterable<string, array{bool}> */
    public static function escapeTransitions(): iterable
    {
        yield 'system exit' => [true];
        yield 'system entry' => [false];
    }

    #[DataProvider('escapeTransitions')]
    public function testEscapeCanCrossAnomalousSystemTransitionsWithoutTeleporting(bool $exit): void
    {
        $anchor = $this->mapField(0);
        $fields = $this->systemRow($exit ? 1 : 2, $anchor);
        $anchor->setSystem($fields[0]->getSystem());
        $clear = $this->mapField(1);
        $maps = $exit ? [$anchor, $clear] : [$anchor];
        $this->anomaly($anchor, AnomalyTypeEnum::ION_STORM);
        $this->anomaly($fields[0], AnomalyTypeEnum::ION_STORM);
        $source = $exit ? $fields[0] : $anchor;
        [$navigation, $ship, $configuration, $flights] = $this->movement($source, $fields[0], $fields, $maps, 7);

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertSame($exit ? [$anchor, $clear] : [$fields[0], $fields[1]], $flights->toArray());
    }

    /** @return iterable<string, array{string}> */
    public static function interruptedEscapes(): iterable
    {
        yield 'disabled' => ['disabled'];
        yield 'destroyed' => ['destroyed'];
        yield 'blocked' => ['blocked'];
        yield 'next field becomes unsafe' => ['unsafe'];
    }

    #[DataProvider('interruptedEscapes')]
    public function testEscapeStopsWhenAFlightFailsOrTheSphereCannotContinue(string $outcome): void
    {
        $fields = $this->systemRow(3);
        $this->anomaly($fields[0], AnomalyTypeEnum::ION_STORM);
        $this->anomaly($fields[1], AnomalyTypeEnum::ION_STORM);
        [$navigation, $ship, $configuration, $flights] = $this->movement($fields[0], $fields[2], $fields, [], 7, static function (Ship $ship, Location $destination) use ($outcome, $fields): void {
            if ($outcome !== 'blocked') {
                $ship->setLocation($destination);
            }
            if ($outcome === 'disabled') {
                $ship->getCondition()->setDisabled(true);
            } elseif ($outcome === 'destroyed') {
                $ship->getCondition()->setIsDestroyed(true);
            } elseif ($outcome === 'unsafe') {
                $fields[2]->setFieldType(new MapFieldType()->setPassable(true)->setSpecialDamage(7500));
            }
        });

        self::assertTrue($navigation->evadeAnomaly($ship, $configuration));

        self::assertCount(1, $flights);
        self::assertSame($fields[$outcome === 'blocked' ? 0 : 1], $ship->getLocation());
    }

    private function anomaly(Location $location, AnomalyTypeEnum $type, int $remainingTicks = 1): void
    {
        $anomalyType = $this->mock(AnomalyType::class);
        $anomalyType->shouldReceive('getId')->andReturn($type->value);
        new Anomaly()->setAnomalyType($anomalyType)->setRemainingTicks($remainingTicks)->setLocation($location);
    }

    /** @return list<StarSystemMap> */
    private function systemRow(int $count, ?Map $anchor = null): array
    {
        $system = $this->mock(StarSystem::class);
        $system->shouldReceive('getId')->andReturn(1);
        $system->shouldReceive('getMaxX', 'getMaxY')->andReturn(30);
        $system->shouldReceive('getMap')->andReturn($anchor);
        $fields = [];
        foreach (range(1, $count) as $x) {
            $field = new StarSystemMap()->setSystem($system)->setSx($x)->setSy(2);
            $field->setFieldType(new MapFieldType()->setPassable(true)->setDamage(6));
            $fields[] = $field;
        }
        return $fields;
    }

    private function mapField(int $x): Map
    {
        $field = $this->mock(Map::class)->makePartial();
        $field->shouldReceive('getX')->andReturn($x);
        $field->shouldReceive('getY')->andReturn(0);
        $field->shouldReceive('getLayer')->andReturn(new Layer());
        $field->shouldReceive('getSpacecrafts')->andReturn(new ArrayCollection());
        $field->shouldReceive('getAnomalies')->andReturn(new ArrayCollection());
        $field->setFieldType(new MapFieldType()->setPassable(true)->setDamage(6));
        return $field;
    }

    /**
     * @param list<StarSystemMap> $systemFields
     * @param list<Map> $mapFields
     * @param null|callable(Ship, Location): void $onMove
     * @return array{BorgSphereNavigation, Ship, BorgSphereConfiguration, ArrayCollection<int, Location>}
     */
    private function movement(Map|StarSystemMap $source, StarSystemMap $target, array $systemFields, array $mapFields, int $budget, ?callable $onMove = null): array
    {
        $ship = new Ship();
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(19);
        $ship->setLocation($source)->setCondition(new SpacecraftCondition($ship))->setUser($user);
        $configuration = new BorgSphereConfiguration()->setColony(new Colony()->setStarsystemMap($target))->setSettings(['movement_min_fields' => 1, 'movement_max_fields' => 7, 'roam_radius' => 20]);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 7)->andReturn($budget);
        $random->shouldReceive('array_rand')->andReturn(0);
        $systemMaps = $this->mock(StarSystemMapRepositoryInterface::class);
        $systemMaps->shouldReceive('getByCoordinates')->andReturnUsing(static fn (int $systemId, int $x, int $y): ?StarSystemMap => array_find($systemFields, static fn (StarSystemMap $field): bool => $field->getX() === $x && $field->getY() === $y));
        $systemMaps->shouldReceive('getBySystemOrdered')->andReturn($systemFields);
        $maps = $this->mock(MapRepositoryInterface::class);
        $maps->shouldReceive('getByCoordinates')->andReturnUsing(static fn (?Layer $layer, int $x, int $y): ?Map => array_find($mapFields, static fn (Map $field): bool => $field->getX() === $x && $field->getY() === $y));
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldReceive('wrapShip')->with($ship)->andReturn($this->mock(ShipWrapperInterface::class));
        $alertWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $alertWrapper->shouldReceive('getAlertState')->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $wrappers->shouldReceive('wrapSpacecraft')->andReturn($alertWrapper);
        $flights = new ArrayCollection();
        $flight = $this->movingFlight($ship, [...$systemFields, ...$mapFields], $wrappers, $flights, $onMove);
        return [new BorgSphereNavigation($maps, $systemMaps, $wrappers, $flight, $random), $ship, $configuration, $flights];
    }

    /**
     * @param list<Location> $fields
     * @param SpacecraftWrapperFactoryInterface&MockInterface $wrappers
     * @param ArrayCollection<int, Location> $flights
     * @param null|callable(Ship, Location): void $onMove
     */
    private function movingFlight(Ship $ship, array $fields, SpacecraftWrapperFactoryInterface $wrappers, ArrayCollection $flights, ?callable $onMove): BorgSphereFlight
    {
        $destination = null;
        $route = $this->mock(FlightRouteInterface::class);
        $route->shouldReceive('hasSpecialDamageOnField')->andReturn(false);
        $routes = $this->mock(FlightRouteFactoryInterface::class);
        $routes->shouldReceive('getRouteForCoordinateDestination')->andReturnUsing(static function (Ship $ship, int $x, int $y) use ($fields, &$destination, $route): FlightRouteInterface {
            $destination = array_find($fields, static fn (Location $field): bool => $field::class === $ship->getLocation()::class && $field->getX() === $x && $field->getY() === $y);
            return $route;
        });
        $routes->shouldReceive('getRouteForMapDestination')->andReturnUsing(static function (Location $field) use (&$destination, $route): FlightRouteInterface {
            $destination = $field;
            return $route;
        });
        $mover = $this->mock(ShipMoverInterface::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);
        $mover->shouldReceive('checkAndMove')->andReturnUsing(static function () use ($ship, &$destination, $flights, $onMove, $messageCollection): MessageCollectionInterface {
            self::assertNotNull($destination);
            $flights->add($destination);
            if ($onMove === null) {
                $ship->setLocation($destination);
            } else {
                $onMove($ship, $destination);
            }
            return $messageCollection;
        });
        $messages = $this->mock(DistributedMessageSenderInterface::class);
        $messages->shouldReceive('distributeMessageCollection');
        return new BorgSphereFlight($mover, $routes, $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $messages);
    }

    private function navigation(?StarSystemMapRepositoryInterface $systemMaps = null, ?SpacecraftWrapperFactoryInterface $wrappers = null): BorgSphereNavigation
    {
        $wrappers ??= $this->mock(SpacecraftWrapperFactoryInterface::class);
        $flight = new BorgSphereFlight(
            $this->mock(ShipMoverInterface::class),
            $this->mock(FlightRouteFactoryInterface::class),
            $wrappers,
            $this->mock(SpacecraftSystemManagerInterface::class),
            $this->mock(DistributedMessageSenderInterface::class)
        );
        return new BorgSphereNavigation(
            $this->mock(MapRepositoryInterface::class),
            $systemMaps ?? $this->mock(StarSystemMapRepositoryInterface::class),
            $wrappers,
            $flight,
            $this->mock(StuRandom::class)
        );
    }

}
