<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class SkipDetectionTest extends StuTestCase
{
    private MockInterface&PlayerRelationDeterminatorInterface $playerRelationDeterminator;
    private MockInterface&StuTime $stuTime;

    private MockInterface&Ship $incomingShip;
    private MockInterface&SpacecraftWrapperInterface $alertedWrapper;
    private MockInterface&Ship $alertedShip;

    private SkipDetectionInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->playerRelationDeterminator = $this->mock(PlayerRelationDeterminatorInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->incomingShip = $this->mock(Ship::class);
        $this->alertedWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $this->alertedShip = $this->mock(Ship::class);

        $this->alertedWrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->alertedShip);

        $this->subject = new SkipDetection(
            $this->playerRelationDeterminator,
            $this->stuTime
        );
    }

    public function testIsSkippedExpectTrueWhenAlertYellowAndNotEnemy(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);

        $this->playerRelationDeterminator->shouldReceive('isEnemy')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectTrueAndTrojanNoticeWhenTractoredByFriend(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);

        $tractoringShip = $this->mock(Ship::class);
        $tractoringShipUser = $this->mock(User::class);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);

        $tractoringShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoringShipUser);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $tractoringShipUser)
            ->once()
            ->andReturn(true);
        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            $tractoringShip,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertFalse($usersToInformAboutTrojanHorse->isEmpty());
        $this->assertTrue($usersToInformAboutTrojanHorse->contains($alertUser));
    }

    public function testIsSkippedExpectTrueAndNoTrojanNoticeWhenTractoredByFriendButAlreadyNoticed(): void
    {
        $alertUser = $this->mock(User::class);
        $usersToInformAboutTrojanHorse = new ArrayCollection([$alertUser]);
        $incomingShipUser = $this->mock(User::class);

        $tractoringShip = $this->mock(Ship::class);
        $tractoringShipUser = $this->mock(User::class);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);

        $tractoringShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoringShipUser);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $tractoringShipUser)
            ->once()
            ->andReturn(true);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            $tractoringShip,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEquals(1, $usersToInformAboutTrojanHorse->count());
    }

    public function testIsSkippedExpectTrueWhenIsFriend(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(true);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectTrueWhenInFinishedWeb(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $finishedWeb = $this->mock(TholianWeb::class);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($finishedWeb);

        $finishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->playerRelationDeterminator->shouldReceive('isEnemy')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(true);
        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectTrueWhenAlertIsPirateAndNewUser(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(4_838_400);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectTrueWhenAlertIsPirateAndProtectionExists(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);
        $pirateWrath = $this->mock(PirateWrath::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn($pirateWrath);

        $pirateWrath->shouldReceive('getProtectionTimeout')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_043);
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NPC_KAZON);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectTrueWhenIncomingIsPirateAndProtectionExists(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);
        $pirateWrath = $this->mock(PirateWrath::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $incomingShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NPC_KAZON);

        $alertUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn($pirateWrath);

        $pirateWrath->shouldReceive('getProtectionTimeout')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_043);
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertTrue($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectFalseWhenAlertIsPirateAndNoProtection(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);
        $pirateWrath = $this->mock(PirateWrath::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn($pirateWrath);
        $incomingShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $alertUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $pirateWrath->shouldReceive('getProtectionTimeout')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NPC_KAZON);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertFalse($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectFalseWhenAlertIsPirateAndNoWrath(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $incomingShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $alertUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NPC_KAZON);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertFalse($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectFalseWhenIncomingIsPirateAndNoProtection(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);
        $pirateWrath = $this->mock(PirateWrath::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $incomingShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NPC_KAZON);

        $alertUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn($pirateWrath);

        $pirateWrath->shouldReceive('getProtectionTimeout')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertFalse($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectFalseWhenIncomingIsPirateAndNoWrath(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);
        $unfinishedWeb = $this->mock(TholianWeb::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $incomingShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NPC_KAZON);

        $alertUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($unfinishedWeb);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $unfinishedWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertFalse($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }

    public function testIsSkippedExpectFalse(): void
    {
        $usersToInformAboutTrojanHorse = new ArrayCollection();
        $alertUser = $this->mock(User::class);
        $incomingShipUser = $this->mock(User::class);

        $incomingShipUser->shouldReceive('getRegistration->getCreationDate')
            ->withNoArgs()
            ->once()
            ->andReturn(161_642);
        $incomingShipUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $incomingShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $alertUser->shouldReceive('getPirateWrath')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(5_000_042);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($incomingShipUser);

        $this->alertedShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($alertUser);
        $this->alertedWrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->alertedShip->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->alertedShip->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($alertUser, $incomingShipUser)
            ->once()
            ->andReturn(false);

        $result = $this->subject->isSkipped(
            $this->incomingShip,
            $this->alertedWrapper,
            null,
            $usersToInformAboutTrojanHorse
        );

        $this->assertFalse($result);
        $this->assertEmpty($usersToInformAboutTrojanHorse);
    }
}
