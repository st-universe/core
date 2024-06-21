<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class RoundBasedBattlePartyTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private $shipRepository;
    /** @var MockInterface|BattlePartyInterface */
    private $battleParty;

    private RoundBasedBattleParty $subject;

    public function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->battleParty = $this->mock(BattlePartyInterface::class);
    }

    public function testGet(): void
    {
        $this->battleParty->shouldReceive('getActiveMembers->getKeys')
            ->withNoArgs()
            ->once()
            ->andReturn([0, 1, 2]);


        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $result = $this->subject->get();

        $this->assertSame($this->battleParty, $result);
    }

    public function testGetAllUnusedThatCanFire(): void
    {
        $wrapper0 = $this->mock(ShipWrapperInterface::class);
        $wrapper1 = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);

        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(true)
            ->twice()
            ->andReturn(new ArrayCollection([
                0 => $wrapper0,
                1 => $wrapper1,
                2 => $wrapper2
            ]), new ArrayCollection([
                1 => $wrapper1,
                2 => $wrapper2
            ]));

        $wrapper1->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $wrapper2->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $this->subject->use(1);
        $result = $this->subject->getAllUnusedThatCanFire();

        $this->assertEquals([2 => $wrapper2], $result->toArray());
    }

    public function testIsDoneExpectTrueWhenNoUnusedIdsLeft(): void
    {
        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(true)
            ->once()
            ->andReturn(new ArrayCollection([]));

        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $result = $this->subject->isDone();

        $this->assertTrue($result);
    }

    public function testIsDoneExpectTrueWhenAllShipsUsed(): void
    {
        $wrapper0 = $this->mock(ShipWrapperInterface::class);

        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(true)
            ->once()
            ->andReturn(new ArrayCollection([
                0 => $wrapper0
            ]));

        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $this->subject->use(0);
        $result = $this->subject->isDone();

        $this->assertTrue($result);
    }

    public function testIsDoneExpectTrueWhenNoOneCanFire(): void
    {
        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(true)
            ->once()
            ->andReturn(new ArrayCollection([]));

        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $result = $this->subject->isDone();

        $this->assertTrue($result);
    }

    public function testGetRandomUnused(): void
    {
        $wrapper0 = $this->mock(ShipWrapperInterface::class);

        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(true)
            ->twice()
            ->andReturn(new ArrayCollection([
                0 => $wrapper0
            ]));

        $wrapper0->shouldReceive('get->getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(0);

        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $result = $this->subject->getRandomUnused();

        $this->assertSame($wrapper0, $result);
        $this->assertTrue($this->subject->isDone());
    }

    public function testSaveActiveMembers(): void
    {
        $wrapper0 = $this->mock(ShipWrapperInterface::class);
        $wrapper1 = $this->mock(ShipWrapperInterface::class);
        $ship0 = $this->mock(ShipInterface::class);
        $ship1 = $this->mock(ShipInterface::class);

        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(true)
            ->once()
            ->andReturn(new ArrayCollection([
                0 => $wrapper0,
                1 => $wrapper1
            ]));
        $this->battleParty->shouldReceive('getActiveMembers')
            ->with(false, false)
            ->once()
            ->andReturn(new ArrayCollection([
                0 => $wrapper0,
                1 => $wrapper1
            ]));

        $wrapper0->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship0);
        $wrapper1->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship1);

        $this->shipRepository->shouldReceive('save')
            ->with($ship0)
            ->once();
        $this->shipRepository->shouldReceive('save')
            ->with($ship1)
            ->once();

        $this->subject =  new RoundBasedBattleParty(
            $this->battleParty,
            $this->shipRepository
        );

        $this->subject->saveActiveMembers();
    }
}
