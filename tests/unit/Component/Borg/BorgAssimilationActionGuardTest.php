<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationActionGuardTest extends StuTestCase
{
    /** @return iterable<string, array{array<string, mixed>}> */
    public static function mutationParameters(): iterable
    {
        yield 'primary ship' => [['id' => 77]];
        yield 'alternate ship' => [['shipid' => 77]];
        yield 'shuttle' => [['shuttle' => 77]];
        yield 'mass selection' => [['chosen' => [77]]];
        yield 'warpcore selection' => [['spacecrafts' => [77]]];
    }

    #[DataProvider('mutationParameters')]
    public function testCapturedOwnShipsCannotBeMutatedThroughIndividualOrMassActions(array $parameters): void
    {
        request::setMockVars($parameters);
        $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('getUser->getId')->andReturn(261);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(true);
        $spacecrafts->shouldReceive('find')->with(77)->once()->andReturn($ship);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $guard = new BorgAssimilationActionGuard($spacecrafts, $this->mock(CrewAssignmentRepositoryInterface::class));

        $this->expectException(EntityLockedException::class);
        $this->expectExceptionMessage('Das Schiff wird assimiliert und kann bis zu seiner Befreiung nicht gesteuert oder verändert werden');
        $guard->check(ModuleEnum::SHIP, 'B_SELFDESTRUCT', $user);
    }

    public function testCrewMutationCannotBypassTheShipLock(): void
    {
        request::setMockVars(['id' => 555]);
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('getUser->getId')->andReturn(261);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(true);
        $assignment = $this->mock(CrewAssignment::class);
        $assignment->shouldReceive('getSpacecraft')->once()->andReturn($ship);
        $assignments = $this->mock(CrewAssignmentRepositoryInterface::class);
        $assignments->shouldReceive('findOneBy')->with(['crew' => 555])->once()->andReturn($assignment);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);

        $this->expectException(EntityLockedException::class);
        new BorgAssimilationActionGuard($this->mock(SpacecraftRepositoryInterface::class), $assignments)->check(ModuleEnum::SHIP, 'B_RENAME_CREWMAN', $user);
    }

    /** @return iterable<string, array{ModuleEnum, string, array<string, mixed>}> */
    public static function otherModules(): iterable
    {
        yield 'colony repairs' => [ModuleEnum::COLONY, 'B_REPAIR_SHIP', ['id' => 123, 'ship_id' => 77]];
        yield 'colony orbital management' => [ModuleEnum::COLONY, 'B_MANAGE_SPACECRAFTS', ['id' => 123, 'spacecrafts' => [77]]];
        yield 'global cargo crew or torpedo transfer' => [ModuleEnum::GAME, 'B_TRANSFER', ['id' => 123, 'source_type' => 'colony', 'target' => 77, 'target_type' => 'ship']];
        yield 'external boarding' => [ModuleEnum::SHIP, 'B_BOARD_SHIP', ['id' => 123, 'target' => 77]];
        yield 'external takeover' => [ModuleEnum::SHIP, 'B_TAKEOVER_SHIP', ['id' => 123, 'target' => 77]];
        yield 'external tractor beam' => [ModuleEnum::SHIP, 'B_ACTIVATE_TRACTOR', ['id' => 123, 'target' => 77]];
    }

    #[DataProvider('otherModules')]
    public function testColonyAndGlobalTransfersCannotBypassTheCapturedShipLock(ModuleEnum $module, string $action, array $parameters): void
    {
        request::setMockVars($parameters);
        $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(true);
        $ship->shouldReceive('getUser->getId')->andReturn(262);
        $spacecrafts->shouldReceive('find')->with(77)->once()->andReturn($ship);
        $spacecrafts->shouldReceive('find')->with(123)->andReturnNull();
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);

        $this->expectException(EntityLockedException::class);
        $this->expectExceptionMessage('Mit dem Schiff kann während der Assimilation nicht interagiert werden');
        new BorgAssimilationActionGuard($spacecrafts, $this->mock(CrewAssignmentRepositoryInterface::class))->check($module, $action, $user);
    }

    public function testAdminActionsAndUnrelatedModulesDoNotInspectShipIds(): void
    {
        request::setMockVars(['id' => 77]);
        $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
        $spacecrafts->shouldNotReceive('find');
        $guard = new BorgAssimilationActionGuard($spacecrafts, $this->mock(CrewAssignmentRepositoryInterface::class));
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        foreach ([ModuleEnum::ADMIN, ModuleEnum::COLONY, ModuleEnum::OPTIONS] as $module) {
            $guard->check($module, 'ACTION', $user);
        }
    }

    public function testNormalAttacksCanTargetShipsBeingAssimilated(): void
    {
        request::setMockVars(['id' => 123, 'target' => 77]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $source = $this->mock(Spacecraft::class);
        $source->shouldReceive('getUser')->andReturn($user);
        $source->shouldReceive('isBeingAssimilated')->once()->andReturn(false);
        $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
        $spacecrafts->shouldReceive('find')->once()->with(123)->andReturn($source);
        $spacecrafts->shouldNotReceive('find')->with(77);

        new BorgAssimilationActionGuard($spacecrafts, $this->mock(CrewAssignmentRepositoryInterface::class))->check(ModuleEnum::SHIP, 'B_ATTACK_SPACECRAFT', $user);
    }

    public function testCapturedAttackerCannotFireEvenThoughCapturedTargetsAreAttackable(): void
    {
        request::setMockVars(['id' => 77, 'target' => 123]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $source = $this->mock(Spacecraft::class);
        $source->shouldReceive('getUser')->andReturn($user);
        $source->shouldReceive('isBeingAssimilated')->once()->andReturn(true);
        $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
        $spacecrafts->shouldReceive('find')->once()->with(77)->andReturn($source);

        $this->expectException(EntityLockedException::class);
        $this->expectExceptionMessage('Das Schiff wird assimiliert und kann bis zu seiner Befreiung nicht gesteuert oder verändert werden');
        new BorgAssimilationActionGuard($spacecrafts, $this->mock(CrewAssignmentRepositoryInterface::class))->check(ModuleEnum::SHIP, 'B_ATTACK_SPACECRAFT', $user);
    }

    public function testUnownedOrUnspecifiedInteractionUsesNoSteeringMessage(): void
    {
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(true);
        $ship->shouldNotReceive('getUser');

        $this->expectException(EntityLockedException::class);
        $this->expectExceptionMessage('Mit dem Schiff kann während der Assimilation nicht interagiert werden');
        BorgAssimilationActionGuard::assertUsable($ship);
    }

    public function testFreedShipsCanBeUsedAgain(): void
    {
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(false);

        BorgAssimilationActionGuard::assertUsable($ship);
    }

    public function testAnotherShipOfTheSamePlayerRemainsUsable(): void
    {
        request::setMockVars(['id' => 78]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('getUser')->andReturn($user);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(false);
        $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
        $spacecrafts->shouldReceive('find')->once()->with(78)->andReturn($ship);
        $spacecrafts->shouldNotReceive('find')->with(77);

        new BorgAssimilationActionGuard($spacecrafts, $this->mock(CrewAssignmentRepositoryInterface::class))->check(ModuleEnum::SHIP, 'B_ACTIVATE_NBS', $user);
    }
}
