<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgSphereInterceptionTest extends StuTestCase
{
    private InterceptShipCoreInterface&MockInterface $interception;
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private PlayerRelationDeterminatorInterface&MockInterface $relations;
    private SpacecraftAttackCoreInterface&MockInterface $attacks;
    private FightLibInterface&MockInterface $fight;
    private StuRandom&MockInterface $random;
    private BorgSphereAttackContext $context;
    private BorgSphereInterception $subject;
    private StarSystemMap $location;
    private Ship $sphere;
    private BorgSphereEvent $event;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->interception = $this->mock(InterceptShipCoreInterface::class);
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $this->attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $this->fight = $this->mock(FightLibInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $this->context = new BorgSphereAttackContext();
        $battle = new BorgSphereBattle($this->attacks, $this->wrappers, $this->relations, $this->fight, $this->context);
        $this->subject = new BorgSphereInterception($this->interception, $this->wrappers, $this->relations, $battle, $this->context, $this->random);
        $this->location = new StarSystemMap();
        $this->sphere = $this->ship(1, 19, false);
        $this->event = new BorgSphereEvent()->setPhase(BorgSpherePhaseEnum::ROAMING)->setConfiguration(new BorgSphereConfiguration()->setSettings(['intercept_chance_percent' => 40]));
        $this->relations->shouldReceive('isFriend')->andReturnUsing(static fn (User $source, User $target): bool => $target->getId() === 999);
    }

    /** @return iterable<string, array{bool}> */
    public static function targetGroups(): iterable
    {
        yield 'two ships in one fleet' => [false];
        yield 'fleet and independent ship' => [true];
    }

    #[DataProvider('targetGroups')]
    public function testFortyPercentBoundaryInterceptsExactlyOneRandomFleetGroupAndAttacksOnce(bool $independentShip): void
    {
        $first = $this->ship(2, 261);
        $fleetMate = $this->ship(3, 261);
        if ($independentShip) {
            $this->ship(4, 262);
        }
        $fleet = new Fleet();
        new ReflectionProperty(Fleet::class, 'id')->setValue($fleet, 88);
        $first->setFleet($fleet);
        $fleetMate->setFleet($fleet);
        $source = $this->wrapper($this->sphere);
        $target = $this->wrapper($first);
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn(40);
        $this->random->shouldReceive('array_rand')->once()->andReturnUsing(static function (array $groups) use ($independentShip): string {
            self::assertSame($independentShip ? ['fleet:88', 'ship:4'] : ['fleet:88'], array_keys($groups));
            return 'fleet:88';
        });
        $this->interception->shouldReceive('intercept')->once()->with($source, $target, Mockery::type(InformationWrapper::class), false, true)
            ->andReturnUsing(function () use ($first): void {
                $first->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            });
        $this->fight->shouldReceive('canAttackTarget')->once()->with($this->sphere, $first, true)->andReturn(true);
        $this->attacks->shouldReceive('attack')->once()->with($source, $target, false, false, Mockery::type(InformationWrapper::class))->andReturn(true);

        self::assertTrue($this->subject->interceptAndAttack($this->event, $this->sphere));
        self::assertTrue($fleetMate->isWarped());
        self::assertFalse($this->context->isBattleStopped());
    }

    public function testFortyOnePercentRollDoesNotInterceptOrSelectAGroup(): void
    {
        $this->ship(2, 261);
        $this->ship(3, 262);
        $this->wrapper($this->sphere);
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn(41);
        $this->random->shouldNotReceive('array_rand');
        $this->interception->shouldNotReceive('intercept');
        $this->attacks->shouldNotReceive('attack');

        self::assertFalse($this->subject->interceptAndAttack($this->event, $this->sphere));
    }

    public function testIneligibleShipsDoNotCountTowardTheTwoWarpShipMinimum(): void
    {
        $this->ship(2, 261);
        $this->ship(3, 999);
        $this->ship(4, 19);
        $this->ship(5, 262, false);
        $vacation = $this->ship(6, 263);
        $vacation->getUser()->setVacationMode(true)->setVacationRequestDate(1);
        $cloaked = $this->ship(7, 264);
        $cloaked->getSystems()->set(SpacecraftSystemTypeEnum::CLOAK->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::CLOAK)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        $this->ship(8, 265)->getCondition()->setDisabled(true);
        $this->ship(9, 266)->getCondition()->setIsDestroyed(true);
        $this->ship(10, 267)->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::ESCAPE_PODS);
        $invisible = $this->ship(11, 268);
        $invisible->getSystems()->set(SpacecraftSystemTypeEnum::RPG_MODULE->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::RPG_MODULE)->setData('{"invisible":true}'));
        $this->ship(12, 269)->setLocation(new StarSystemMap());
        $this->wrapper($this->sphere);
        $this->random->shouldNotReceive('rand', 'array_rand');
        $this->interception->shouldNotReceive('intercept');
        $this->attacks->shouldNotReceive('attack');

        self::assertFalse($this->subject->interceptAndAttack($this->event, $this->sphere));
    }

    /** @return iterable<string, array{bool}> */
    public static function interceptionOutcomes(): iterable
    {
        yield 'sphere disabled by alert reaction' => [false];
        yield 'sphere destroyed by alert reaction' => [true];
    }

    #[DataProvider('interceptionOutcomes')]
    public function testAlertReactionCanPreventTheFollowupAttackAndAlwaysClearsTheScope(bool $destroyed): void
    {
        $targetShip = $this->ship(2, 261);
        $this->ship(3, 262);
        $source = $this->wrapper($this->sphere);
        $target = $this->wrapper($targetShip);
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn(1);
        $this->random->shouldReceive('array_rand')->once()->andReturn('ship:2');
        $remote = new Ship()->setLocation(new StarSystemMap());
        $this->interception->shouldReceive('intercept')->once()->with($source, $target, Mockery::type(InformationWrapper::class), false, true)
            ->andReturnUsing(function () use ($targetShip, $destroyed, $remote): void {
                self::assertFalse($this->context->allowsAutomaticAttackTarget($remote));
                $targetShip->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
                $this->sphere->getCondition()->setDisabled(!$destroyed)->setIsDestroyed($destroyed);
            });
        $this->attacks->shouldNotReceive('attack');

        self::assertTrue($this->subject->interceptAndAttack($this->event, $this->sphere));
        self::assertTrue($this->context->allowsAutomaticAttackTarget($remote));
    }

    /** @return iterable<string, array{string}> */
    public static function inactiveStates(): iterable
    {
        foreach (['disabled', 'destroyed', 'regenerating', 'escaped', 'warp', 'cloak', 'damagedWarp', 'crew', 'weapons'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('inactiveStates')]
    public function testUnavailableSphereDoesNotRollOrIntercept(string $state): void
    {
        if ($state === 'disabled' || $state === 'destroyed') {
            $this->sphere->getCondition()->setDisabled($state === 'disabled')->setIsDestroyed($state === 'destroyed');
        } elseif ($state === 'regenerating' || $state === 'escaped') {
            $this->event->setPhase($state === 'escaped' ? BorgSpherePhaseEnum::ESCAPED : BorgSpherePhaseEnum::REGENERATING);
        } elseif ($state === 'crew') {
            $this->sphere->setBuildplan(new SpacecraftBuildplan()->setCrew(1));
        } elseif ($state === 'weapons') {
            $this->wrapper($this->sphere, false);
        } else {
            $type = $state === 'cloak' ? SpacecraftSystemTypeEnum::CLOAK : SpacecraftSystemTypeEnum::WARPDRIVE;
            $this->sphere->getSystems()->set($type->value, new SpacecraftSystem()->setSystemType($type)->setStatus($state === 'damagedWarp' ? 0 : 100)->setMode($state === 'damagedWarp' ? SpacecraftSystemModeEnum::MODE_OFF : SpacecraftSystemModeEnum::MODE_ON));
        }
        $this->random->shouldNotReceive('rand', 'array_rand');
        $this->interception->shouldNotReceive('intercept');

        self::assertFalse($this->subject->interceptAndAttack($this->event, $this->sphere));
    }

    private function ship(int $id, int $userId, bool $warped = true): Ship
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, $userId);
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setUser($user)->setLocation($this->location)->setCondition(new SpacecraftCondition($ship))
            ->setBuildplan(new SpacecraftBuildplan()->setCrew(0))->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::WARPDRIVE->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::WARPDRIVE)->setStatus(100)->setMode($warped ? SpacecraftSystemModeEnum::MODE_ON : SpacecraftSystemModeEnum::MODE_OFF));
        $this->location->getSpacecrafts()->add($ship);
        return $ship;
    }

    private function wrapper(Ship $ship, bool $canFire = true): ShipWrapperInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn($canFire);
        $this->wrappers->shouldReceive('wrapShip')->with($ship)->andReturn($wrapper);
        return $wrapper;
    }
}
