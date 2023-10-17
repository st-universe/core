<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Mockery;
use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipTakeoverRepositoryInterface;
use Stu\StuTestCase;

class ShipTakeoverManagerTest extends StuTestCase
{
    /** @var MockInterface|ShipTakeoverRepositoryInterface */
    private MockInterface $shipTakeoverRepository;

    /** @var MockInterface|ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface|CreatePrestigeLogInterface */
    private MockInterface $createPrestigeLog;

    /** @var MockInterface|PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    /** @var MockInterface|GameControllerInterface */
    private MockInterface $game;

    /** @var MockInterface|ShipInterface */
    private MockInterface $ship;
    /** @var MockInterface|ShipInterface */
    private MockInterface $target;

    private ShipTakeoverManagerInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->shipTakeoverRepository = $this->mock(ShipTakeoverRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        //params
        $this->ship = $this->mock(ShipInterface::class);
        $this->target = $this->mock(ShipInterface::class);

        $this->subject = new ShipTakeoverManager(
            $this->shipTakeoverRepository,
            $this->shipRepository,
            $this->createPrestigeLog,
            $this->privateMessageSender,
            $this->game
        );
    }

    public function testGetPrestigeForBoardingAttemptExpectBaseCostIfNoBuildplan(): void
    {
        $this->target->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getPrestigeForBoardingAttempt($this->target);

        $this->assertEquals(20, $result);
    }

    public function testGetPrestigeForBoardingAttemptExpectCostBasedOnModules(): void
    {
        $buildplan = $this->mock(ShipBuildplanInterface::class);
        $buildplanModule1 = $this->mock(BuildplanModuleInterface::class);
        $buildplanModule2 = $this->mock(BuildplanModuleInterface::class);

        $this->target->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);

        $buildplan->shouldReceive('getModules->toArray')
            ->withNoArgs()
            ->once()
            ->andReturn([$buildplanModule1, $buildplanModule2]);

        $buildplanModule1->shouldReceive('getModule->getLevel')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $buildplanModule2->shouldReceive('getModule->getLevel')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getPrestigeForBoardingAttempt($this->target);

        $this->assertEquals(69, $result);
    }

    public function testStartTakeover(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);
        $user = $this->mock(UserInterface::class);
        $targetUser = $this->mock(UserInterface::class);

        $currentTurn = 42;

        $takeover->shouldReceive('setSourceShip')
            ->with($this->ship)
            ->once()
            ->andReturnSelf();
        $takeover->shouldReceive('setTargetShip')
            ->with($this->target)
            ->once()
            ->andReturnSelf();
        $takeover->shouldReceive('setPrestige')
            ->with(999)
            ->once()
            ->andReturnSelf();
        $takeover->shouldReceive('setStartTurn')
            ->with($currentTurn)
            ->once();

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('setTakeoverActive')
            ->with($takeover);

        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');
        $targetUser->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGETUSER');

        $this->shipTakeoverRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($takeover);
        $this->shipTakeoverRepository->shouldReceive('save')
            ->with($takeover)
            ->once();

        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->once()
            ->andReturn($currentTurn);

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(
                -999,
                '-999 Prestige erhalten für den Start der Übernahme der TARGET von Spieler TARGETUSER',
                $user,
                Mockery::any()
            )
            ->once();

        $this->subject->startTakeover($this->ship, $this->target, 999);
    }

    public function testIsTakeoverReadyExpectTrueWhenReady(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);

        $takeover->shouldReceive('getStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(52);

        $result = $this->subject->isTakeoverReady($takeover);

        $this->assertTrue($result);
    }

    public function testIsTakeoverReadyExpectFalseWhenNotFinished(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);

        $takeover->shouldReceive('getStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $takeover->shouldReceive('getSourceShip')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetShip')
            ->withNoArgs()
            ->andReturn($this->target);

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(666);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');
        $this->target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2);
        $this->target->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(777);

        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(51);

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                'Die Übernahme der TARGET durch die SHIP erfolgt in 1 Runde(n).',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=2'
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                'Die Übernahme der TARGET durch die SHIP erfolgt in 1 Runde(n).',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=1'
            )
            ->once();

        $result = $this->subject->isTakeoverReady($takeover);

        $this->assertFalse($result);
    }

    public function testCancelTakeoverExpectNothingWhenTakeoverIsNull(): void
    {
        $this->shipTakeoverRepository->shouldNotHaveBeenCalled();

        $this->subject->cancelTakeover(null, null);
    }

    public function testCancelTakeoverExpectNothingWhenTargetIsTractoredBySource(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);

        $takeover->shouldReceive('getSourceShip')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetShip')
            ->withNoArgs()
            ->andReturn($this->target);

        $this->target->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->shipTakeoverRepository->shouldNotHaveBeenCalled();

        $this->subject->cancelTakeover(null, null);
    }

    public function testCancelTakeoverExpectCancelWhenTargetTractoredByOtherShip(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);
        $user = $this->mock(UserInterface::class);
        $targetUser = $this->mock(UserInterface::class);

        $takeover->shouldReceive('getSourceShip')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetShip')
            ->withNoArgs()
            ->andReturn($this->target);
        $takeover->shouldReceive('getPrestige')
            ->withNoArgs()
            ->andReturn(1234);

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('setTakeoverActive')
            ->with(null);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(666);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('USER');

        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');
        $this->target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $this->target->shouldReceive('setTakeoverPassive')
            ->with(null);
        $this->target->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn($this->mock(ShipInterface::class));
        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(777);
        $targetUser->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGETUSER');

        $this->shipTakeoverRepository->shouldReceive('delete')
            ->with($takeover)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(
                1234,
                '1234 Prestige erhalten für Abbruch der Übernahme der TARGET von Spieler TARGETUSER',
                $user,
                Mockery::any()
            )
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                'Die Übernahme der TARGET wurde abgebrochen',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=2'
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                'Die Übernahme der TARGET wurde abgebrochen',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=1'
            )
            ->once();

        $this->subject->cancelTakeover($takeover);
    }

    public function testCancelTakeoverExpectCancelWhenTargetNotTractored(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);
        $user = $this->mock(UserInterface::class);
        $targetUser = $this->mock(UserInterface::class);

        $takeover->shouldReceive('getSourceShip')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetShip')
            ->withNoArgs()
            ->andReturn($this->target);
        $takeover->shouldReceive('getPrestige')
            ->withNoArgs()
            ->andReturn(1234);

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('setTakeoverActive')
            ->with(null);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(666);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('USER');

        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');
        $this->target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $this->target->shouldReceive('setTakeoverPassive')
            ->with(null);
        $this->target->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(777);
        $targetUser->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGETUSER');

        $this->shipTakeoverRepository->shouldReceive('delete')
            ->with($takeover)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(
                1234,
                '1234 Prestige erhalten für Abbruch der Übernahme der TARGET von Spieler TARGETUSER',
                $user,
                Mockery::any()
            )
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                sprintf(
                    'Die Übernahme der TARGET wurde abgebrochen%s',
                    'CAUSE'
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=2'
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                sprintf(
                    'Die Übernahme der TARGET wurde abgebrochen%s',
                    'CAUSE'
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=1'
            )
            ->once();

        $this->subject->cancelTakeover($takeover, 'CAUSE');
    }

    public function testFinishTakeover(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);
        $user = $this->mock(UserInterface::class);
        $targetUser = $this->mock(UserInterface::class);

        $takeover->shouldReceive('getSourceShip')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetShip')
            ->withNoArgs()
            ->andReturn($this->target);

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('setTakeoverActive')
            ->with(null);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(666);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('USER');

        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');
        $this->target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $this->target->shouldReceive('setUser')
            ->with($user)
            ->once();
        $this->target->shouldReceive('setTakeoverPassive')
            ->with(null);
        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(777);
        $targetUser->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGETUSER');

        $this->shipTakeoverRepository->shouldReceive('delete')
            ->with($takeover)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->target)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                'Die TARGET wurde von Spieler USER übernommen',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                null
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                'Die TARGET von Spieler TARGETUSER wurde übernommen',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=2'
            )
            ->once();

        $this->subject->finishTakeover($takeover);
    }
}
