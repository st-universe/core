<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ShipTakeoverRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class ShipTakeoverManagerTest extends StuTestCase
{
    private MockInterface&ShipTakeoverRepositoryInterface $shipTakeoverRepository;
    private MockInterface&StorageRepositoryInterface $storageRepository;
    private MockInterface&CreatePrestigeLogInterface $createPrestigeLog;
    private MockInterface&LeaveFleetInterface $leaveFleet;
    private MockInterface&EntryCreatorInterface $entryCreator;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&GameControllerInterface $game;

    private MockInterface&Ship $ship;
    private MockInterface&Ship $target;

    private ShipTakeoverManagerInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->shipTakeoverRepository = $this->mock(ShipTakeoverRepositoryInterface::class);
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        //params
        $this->ship = $this->mock(Ship::class);
        $this->target = $this->mock(Ship::class);

        $this->subject = new ShipTakeoverManager(
            $this->shipTakeoverRepository,
            $this->storageRepository,
            $this->createPrestigeLog,
            $this->leaveFleet,
            $this->entryCreator,
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

        $this->assertEquals(8, $result);
    }

    public function testGetPrestigeForBoardingAttemptExpectCostBasedOnModules(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $buildplanModule1 = $this->mock(BuildplanModule::class);
        $buildplanModule2 = $this->mock(BuildplanModule::class);

        $this->target->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);

        $buildplan->shouldReceive('getModules')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildplanModule1, $buildplanModule2]));

        $buildplanModule1->shouldReceive('getModule->getLevel')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $buildplanModule2->shouldReceive('getModule->getLevel')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getPrestigeForBoardingAttempt($this->target);

        $this->assertEquals(57, $result);
    }

    public static function startTakeoverTestData(): array
    {
        return [
            [false, "Die SHIP von Spieler USER hat mit der Übernahme der TARGET begonnen.\n\n\nÜbernahme erfolgt in 10 Runden."],
            [true, "Die SHIP von Spieler USER hat mit der Übernahme der TARGET begonnen.\nDie Flotte wurde daher verlassen.\n\nÜbernahme erfolgt in 10 Runden."],
        ];
    }

    #[DataProvider('startTakeoverTestData')]
    public function testStartTakeover(bool $isTargetInFleet, string $expectedMessage): void
    {
        $takeover = $this->mock(ShipTakeover::class);
        $user = $this->mock(User::class);
        $targetUser = $this->mock(User::class);

        $currentTurn = 42;

        $takeover->shouldReceive('setSourceSpacecraft')
            ->with($this->ship)
            ->once()
            ->andReturnSelf();
        $takeover->shouldReceive('getSourceSpacecraft')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $takeover->shouldReceive('setTargetSpacecraft')
            ->with($this->target)
            ->once()
            ->andReturnSelf();
        $takeover->shouldReceive('getTargetSpacecraft')
            ->withNoArgs()
            ->once()
            ->andReturn($this->target);
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
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');
        $this->target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(77);
        $this->target->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($isTargetInFleet ? $this->mock(Fleet::class) : null);
        $targetUser->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGETUSER');

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(666);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('USER');
        $user->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(777);

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

        if ($isTargetInFleet) {
            $this->leaveFleet->shouldReceive('leaveFleet')
                ->with($this->target)
                ->once();
        }

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(
                -999,
                '-999 Prestige abgezogen für den Start der Übernahme der TARGET von Spieler TARGETUSER',
                $user,
                Mockery::any()
            )
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                $expectedMessage,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->target
            )
            ->once();

        $this->subject->startTakeover($this->ship, $this->target, 999);
    }

    public function testIsTakeoverReadyExpectTrueWhenReady(): void
    {
        $takeover = $this->mock(ShipTakeover::class);

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
        $takeover = $this->mock(ShipTakeover::class);

        $takeover->shouldReceive('getStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $takeover->shouldReceive('getSourceSpacecraft')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetSpacecraft')
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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->target
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                'Die Übernahme der TARGET durch die SHIP erfolgt in 1 Runde(n).',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->ship
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
        $takeover = $this->mock(ShipTakeover::class);

        $takeover->shouldReceive('getSourceSpacecraft')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetSpacecraft')
            ->withNoArgs()
            ->andReturn($this->target);

        $this->target->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->shipTakeoverRepository->shouldNotHaveBeenCalled();

        $this->subject->cancelTakeover(null, null);
    }

    public function testCancelTakeoverExpectCancelWhenTargetTractoredByOtherShip(): void
    {
        $takeover = $this->mock(ShipTakeover::class);
        $user = $this->mock(User::class);
        $targetUser = $this->mock(User::class);

        $takeover->shouldReceive('getSourceSpacecraft')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetSpacecraft')
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
            ->with(null)
            ->andReturnSelf();
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(666);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('USER');
        $user->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);

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
        $this->target->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn($this->mock(Ship::class));
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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->target
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                'Die Übernahme der TARGET wurde abgebrochen',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->ship
            )
            ->once();

        $this->subject->cancelTakeover($takeover);
    }

    public function testCancelTakeoverExpectCancelWhenTargetNotTractored(): void
    {
        $takeover = $this->mock(ShipTakeover::class);
        $user = $this->mock(User::class);
        $targetUser = $this->mock(User::class);

        $takeover->shouldReceive('getSourceSpacecraft')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetSpacecraft')
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
            ->with(null)
            ->andReturnSelf();
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(666);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('USER');
        $user->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);

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
        $this->target->shouldReceive('getTractoringSpacecraft')
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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->target
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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->ship
            )
            ->once();

        $this->subject->cancelTakeover($takeover, 'CAUSE');
    }

    public function testFinishTakeover(): void
    {
        $takeover = $this->mock(ShipTakeover::class);
        $user = $this->mock(User::class);
        $targetUser = $this->mock(User::class);
        $storage = $this->mock(Storage::class);
        $storage2 = $this->mock(Storage::class);
        $torpedoStorage = $this->mock(TorpedoStorage::class);
        $boundStorage = $this->mock(Storage::class);

        $takeover->shouldReceive('getSourceSpacecraft')
            ->withNoArgs()
            ->andReturn($this->ship);
        $takeover->shouldReceive('getTargetSpacecraft')
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
            ->with(null)
            ->andReturnSelf();
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();
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
        $this->target->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$storage, $boundStorage]));
        $this->target->shouldReceive('getTorpedoStorage')
            ->withNoArgs()
            ->andReturn($torpedoStorage);
        $this->target->shouldReceive('setUser')
            ->with($user)
            ->once();
        $this->target->shouldReceive('setTakeoverPassive')
            ->with(null);
        $this->target->shouldReceive('getRump->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('RUMP');
        $this->target->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');
        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(777);
        $targetUser->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGETUSER');

        $torpedoStorage->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn($storage2);

        $storage->shouldReceive('setUser')
            ->with($user)
            ->once();
        $storage->shouldReceive('getCommodity->isBoundToAccount')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $boundStorage->shouldReceive('getCommodity->isBoundToAccount')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $storage2->shouldReceive('setUser')
            ->with($user)
            ->once();

        $this->shipTakeoverRepository->shouldReceive('delete')
            ->with($takeover)
            ->once();

        $this->storageRepository->shouldReceive('save')
            ->with($storage)
            ->once();
        $this->storageRepository->shouldReceive('save')
            ->with($storage2)
            ->once();
        $this->storageRepository->shouldReceive('delete')
            ->with($boundStorage)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                'Die TARGET wurde von Spieler USER übernommen',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                null
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                1,
                666,
                'Die TARGET von Spieler TARGETUSER wurde übernommen',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->target
            )
            ->once();

        $this->entryCreator->shouldReceive('addEntry')
            ->with(
                'Die TARGET (RUMP) von Spieler TARGETUSER wurde in Sektor SECTOR durch USER übernommen',
                666,
                $this->target
            )
            ->once();

        $this->subject->finishTakeover($takeover);
    }
}
