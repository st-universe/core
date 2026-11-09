<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use ReflectionProperty;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class IncomingBattlePartyTest extends StuTestCase
{
    private MockInterface&StuRandom $stuRandom;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        //injected
        $this->stuRandom = $this->mock(StuRandom::class);
    }

    public function testGetActiveMembersExpectEmptyWhenCloaked(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new IncomingBattleParty($wrapper, $this->stuRandom);

        $members = $subject->getActiveMembers();

        $this->assertTrue($members->isEmpty());
    }

    public function testGetActiveMembersExpectEmptyWhenWarped(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new IncomingBattleParty($wrapper, $this->stuRandom);

        $members = $subject->getActiveMembers();

        $this->assertTrue($members->isEmpty());
    }

    public function testGetActiveMembersExpectSingleWhenUncloakedAndUnwarped(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new IncomingBattleParty($wrapper, $this->stuRandom);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectUncloakedAndUnwarpedFleetShips(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $wrapperCloaked = $this->mock(ShipWrapperInterface::class);
        $wrapperWarped = $this->mock(ShipWrapperInterface::class);
        $shipCloaked = $this->mock(Ship::class);
        $shipWarped = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapperCloaked->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipCloaked);
        $wrapperWarped->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipWarped);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                12 => $wrapper,
                34 => $wrapperCloaked,
                56 => $wrapperWarped
            ]));

        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $shipCloaked->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $shipWarped->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $shipWarped->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new IncomingBattleParty($wrapper, $this->stuRandom);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper
        ], $members->toArray());
    }

    public function testAutomaticInterceptionReactionExcludesRemoteAndInvisibleFleetMembersUntilScopeIsCleared(): void
    {
        $location = new StarSystemMap();
        $source = new Ship()->setLocation($location);
        $context = new BorgSphereAttackContext();
        $context->beginAutomaticAttack($source);
        $ships = [];
        $wrappers = [];
        for ($id = 1; $id <= 6; $id++) {
            $ship = new Ship();
            new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
            $ship->setUser(new User())->setLocation($location)->setCondition(new SpacecraftCondition($ship))
                ->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
            $wrapper = $this->mock(ShipWrapperInterface::class);
            $wrapper->shouldReceive('get')->andReturn($ship);
            $wrapper->shouldReceive('canFire')->andReturn(true);
            $ships[$id] = $ship;
            $wrappers[$id] = $wrapper;
        }
        $ships[3]->setLocation(new StarSystemMap());
        $ships[4]->getSystems()->set(SpacecraftSystemTypeEnum::RPG_MODULE->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::RPG_MODULE)->setData('{"invisible":true}'));
        $ships[5]->getSystems()->set(SpacecraftSystemTypeEnum::WARPDRIVE->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        $ships[6]->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::ESCAPE_PODS);
        $fleet = $this->mock(FleetWrapperInterface::class);
        $fleet->shouldReceive('getShipWrappers')->once()->andReturn(new ArrayCollection($wrappers));
        $wrappers[1]->shouldReceive('getFleetWrapper')->once()->andReturn($fleet);
        $factory = new BattlePartyFactory($this->mock(SpacecraftWrapperFactoryInterface::class), $this->stuRandom, $context);
        $party = $factory->createIncomingBattleParty($wrappers[1]);

        self::assertSame([1, 2], $party->getActiveMembers()->getKeys());
        self::assertSame([1, 2], $party->getActiveMembers(true)->getKeys());
        $context->clear();
        self::assertSame([1, 2, 3, 4, 6], $party->getActiveMembers()->getKeys());
    }
}
