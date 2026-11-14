<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgTractorRescueParticipantsTest extends StuTestCase
{
    public function testNonLeaderStartsAndTheWholeEligibleFleetFollowsExactlyOnce(): void
    {
        $location = new StarSystemMap();
        $source = $this->ship(1, $location);
        $leader = $this->ship(2, $location)->setIsFleetLeader(true);
        $other = $this->ship(3, $location);
        $fleet = new Fleet()->setLeadShip($leader);
        foreach ([$leader, $source, $other] as $ship) {
            $ship->setFleet($fleet);
        }
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $sourceWrapper = $this->wrapper($source);
        $leaderWrapper = $this->wrapper($leader);
        $otherWrapper = $this->wrapper($other);
        $wrappers->shouldNotReceive('wrapShip')->with($source);
        $wrappers->shouldReceive('wrapShip')->once()->with($leader)->andReturn($leaderWrapper);
        $wrappers->shouldReceive('wrapShip')->once()->with($other)->andReturn($otherWrapper);
        $cube = $this->ship(99, $location, 19);
        $participants = new BorgTractorRescueParticipants($wrappers, $this->webs());

        self::assertFalse($source->isFleetLeader());
        self::assertSame([$sourceWrapper, $leaderWrapper, $otherWrapper], $participants->getParticipants($sourceWrapper, $cube));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidMembers(): iterable
    {
        foreach (['remote', 'other owner', 'warped', 'cloaked', 'destroyed', 'disabled', 'assimilated', 'escape pod', 'RP invisible', 'crew missing', 'energy weapon off', 'zero salvos', 'no EPS', 'no active weapons or NBS', 'outside cube web', 'inside other web'] as $reason) {
            yield $reason => [$reason];
        }
    }

    #[DataProvider('invalidMembers')]
    public function testUnfitFleetMembersCannotParticipate(string $reason): void
    {
        $location = new StarSystemMap();
        $source = $this->ship(1, $location);
        $other = $this->ship(2, $location);
        $fleet = new Fleet()->setLeadShip($source);
        $source->setFleet($fleet);
        $other->setFleet($fleet);
        $this->makeInvalid($other, $reason);
        $sourceWrapper = $this->wrapper($source);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        if (in_array($reason, ['energy weapon off', 'zero salvos', 'no EPS', 'no active weapons or NBS'], true)) {
            $wrappers->shouldReceive('wrapShip')->once()->with($other)->andReturn($this->wrapper($other, $reason !== 'no EPS', $reason !== 'no active weapons or NBS'));
        } else {
            $wrappers->shouldNotReceive('wrapShip')->with($other);
        }
        $webs = $this->webs($other, $reason);
        $participants = new BorgTractorRescueParticipants($wrappers, $webs);

        self::assertSame([$sourceWrapper], $participants->getParticipants($sourceWrapper, $this->ship(99, $location, 19)));
    }

    public function testAnEmptySelectedShipCanStillUseItsEligibleFleetMembers(): void
    {
        $location = new StarSystemMap();
        $source = $this->ship(1, $location);
        $other = $this->ship(2, $location);
        $fleet = new Fleet()->setLeadShip($source);
        $source->setFleet($fleet);
        $other->setFleet($fleet);
        $otherWrapper = $this->wrapper($other);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldReceive('wrapShip')->once()->with($other)->andReturn($otherWrapper);
        $participants = new BorgTractorRescueParticipants($wrappers, $this->webs());

        self::assertSame([$otherWrapper], $participants->getParticipants($this->wrapper($source, false), $this->ship(99, $location, 19)));
    }

    public function testBothShipsInsideTheSameFinishedWebRemainEligible(): void
    {
        $location = new StarSystemMap();
        $source = $this->ship(1, $location);
        $cube = $this->ship(99, $location, 19);
        $web = new TholianWeb()->setFinishedTime(null);
        $source->setHoldingWeb($web);
        $cube->setHoldingWeb($web);
        $wrapper = $this->wrapper($source);
        $webs = $this->mock(TholianWebUtilInterface::class);
        $webs->shouldReceive('isTargetOutsideFinishedTholianWeb')->once()->with($source, $cube)->andReturn(false);
        $webs->shouldReceive('isTargetInsideFinishedTholianWeb')->once()->with($source, $cube)->andReturn(false);
        $participants = new BorgTractorRescueParticipants($this->mock(SpacecraftWrapperFactoryInterface::class), $webs);

        self::assertSame([$wrapper], $participants->getParticipants($wrapper, $cube));
    }

    private function makeInvalid(Ship $ship, string $reason): void
    {
        match ($reason) {
            'remote' => $ship->setLocation(new StarSystemMap()),
            'other owner' => $ship->setUser($this->owner(303)),
            'warped' => $this->system($ship, SpacecraftSystemTypeEnum::WARPDRIVE),
            'cloaked' => $this->system($ship, SpacecraftSystemTypeEnum::CLOAK),
            'destroyed' => $ship->getCondition()->setIsDestroyed(true),
            'disabled' => $ship->getCondition()->setDisabled(true),
            'assimilated' => new SpacecraftAssimilated()->setSpacecraft($ship)->start($ship, 1000, 900),
            'escape pod' => $ship->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::ESCAPE_PODS),
            'RP invisible' => $this->system($ship, SpacecraftSystemTypeEnum::RPG_MODULE)->setData('{"invisible":true}'),
            'crew missing' => $ship->setBuildplan(new SpacecraftBuildplan()->setCrew(10)),
            'energy weapon off' => $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->setMode(SpacecraftSystemModeEnum::MODE_OFF),
            'zero salvos' => $ship->getRump()->setPhaserVolleys(0),
            default => null
        };
    }

    private function ship(int $id, StarSystemMap $location, int $owner = 261): Ship
    {
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setUser($this->owner($owner))->setLocation($location)->setBuildplan(new SpacecraftBuildplan()->setCrew(0));
        $ship->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP)->setPhaserVolleys(3));
        $ship->setCondition(new SpacecraftCondition($ship));
        $this->system($ship, SpacecraftSystemTypeEnum::PHASER);
        return $ship;
    }

    private function owner(int $id): User&MockInterface
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($id);
        return $user;
    }

    private function system(Ship $ship, SpacecraftSystemTypeEnum $type): SpacecraftSystem
    {
        $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $ship->getSystems()->set($type->value, $system);
        return $system;
    }

    private function wrapper(Ship $ship, bool $hasEps = true, bool $canFire = true): ShipWrapperInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn($canFire);
        $eps = $this->mock(EpsSystemData::class);
        $eps->shouldReceive('getEps')->andReturn($hasEps ? 100 : 0);
        $wrapper->shouldReceive('getEpsSystemData')->andReturn($eps);
        return $wrapper;
    }

    private function webs(?Ship $blocked = null, string $reason = ''): TholianWebUtilInterface
    {
        $webs = $this->mock(TholianWebUtilInterface::class);
        $webs->shouldReceive('isTargetOutsideFinishedTholianWeb')->andReturnUsing(static fn (Ship $ship): bool => $ship === $blocked && $reason === 'outside cube web');
        $webs->shouldReceive('isTargetInsideFinishedTholianWeb')->andReturnUsing(static fn (Ship $ship): bool => $ship === $blocked && $reason === 'inside other web');
        return $webs;
    }
}
