<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
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
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
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
        $wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($this->mock(ShipWrapperInterface::class));
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $battle = new BorgSphereBattle($attacks, $wrappers, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(FightLibInterface::class), new BorgSphereAttackContext());
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        new BorgSphereBehavior($navigation, $battle, $random)->work($event, $ship);
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
            $user = $this->mock(User::class);
            $user->shouldReceive('getId')->andReturn($id);
            $user->shouldReceive('isVacationRequestOldEnough')->andReturn(false);
            $ship = new Ship();
            $ship->setUser($user)->setLocation($location)->setCondition(new SpacecraftCondition($ship))
                ->setBuildplan(new SpacecraftBuildplan()->setCrew(0))->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
            $wrapper = $this->mock(ShipWrapperInterface::class);
            $wrapper->shouldReceive('canFire')->andReturn(true);
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
        $event = new BorgSphereEvent()->setPhase($phase);

        $this->behavior($battle, $wrappers)->work($event, $ships[0]);

        self::assertSame($location, $ships[0]->getLocation());
        self::assertSame($phase, $event->getPhase());
    }

    public function testDisabledSphereNeitherAttacksNorMoves(): void
    {
        $ship = new Ship();
        $ship->setCondition(new SpacecraftCondition($ship)->setDisabled(true));
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldNotReceive('wrapShip');
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $battle = new BorgSphereBattle($attacks, $wrappers, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(FightLibInterface::class), new BorgSphereAttackContext());
        $event = new BorgSphereEvent()->setPhase(BorgSpherePhaseEnum::REGENERATING);

        $this->behavior($battle, $wrappers)->work($event, $ship);

        self::assertSame(BorgSpherePhaseEnum::REGENERATING, $event->getPhase());
    }

    private function behavior(BorgSphereBattle $battle, SpacecraftWrapperFactoryInterface $wrappers): BorgSphereBehavior
    {
        $flight = new BorgSphereFlight($this->mock(ShipMoverInterface::class), $this->mock(FlightRouteFactoryInterface::class), $wrappers, $this->mock(SpacecraftSystemManagerInterface::class), $this->mock(DistributedMessageSenderInterface::class));
        $navigation = new BorgSphereNavigation($this->mock(MapRepositoryInterface::class), $this->mock(StarSystemMapRepositoryInterface::class), $wrappers, $flight, $this->mock(StuRandom::class));
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        return new BorgSphereBehavior($navigation, $battle, $random);
    }
}
