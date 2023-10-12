<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Mockery\MockInterface;
use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Lib\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ThreatReactionTest extends StuTestCase
{
    /** @var MockInterface|PlayerRelationDeterminatorInterface */
    private MockInterface $playerRelationDeterminator;

    /** @var MockInterface|ShipAttackCycleInterface */
    private MockInterface $shipAttackCycle;

    /** @var MockInterface|FightLibInterface */
    private MockInterface $fightLib;

    /** @var MockInterface|PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    /** @var MockInterface|GameControllerInterface */
    private MockInterface $game;

    /** @var MockInterface|ShipInterface */
    private MockInterface $ship;
    /** @var MockInterface|ShipInterface */
    private MockInterface $target;

    /** @var MockInterface|ShipWrapperInterface */
    private MockInterface $wrapper;
    /** @var MockInterface|ShipWrapperInterface */
    private MockInterface $targetWrapper;

    private ThreatReactionInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->playerRelationDeterminator = $this->mock(PlayerRelationDeterminatorInterface::class);
        $this->shipAttackCycle = $this->mock(ShipAttackCycleInterface::class);
        $this->fightLib = $this->mock(FightLibInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        //params
        $this->ship = $this->mock(ShipInterface::class);
        $this->target = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->targetWrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);
        $this->targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->target);

        $this->subject = new ThreatReaction(
            $this->playerRelationDeterminator,
            $this->shipAttackCycle,
            $this->fightLib,
            $this->privateMessageSender,
            $this->game
        );
    }

    public function testReactToThreatExpectFalseWhenTargetAlertGreen(): void
    {
        $this->target->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            "REASON"
        );

        $this->assertFalse($result);
    }

    public function testReactToThreatExpectFalseWhenSameUser(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->target->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(424242);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            "REASON"
        );

        $this->assertFalse($result);
    }

    public function testReactToThreatExpectFalseWhenTargetUserIsFriend(): void
    {
        $user = $this->mock(UserInterface::class);
        $user2 = $this->mock(UserInterface::class);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->target->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(424242);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user2);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($user2, $user)
            ->once()
            ->andReturn(true);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            "REASON"
        );

        $this->assertFalse($result);
    }

    public function testReactToThreatExpectFalseWhenEmptyAttackCycle(): void
    {
        $user = $this->mock(UserInterface::class);
        $user2 = $this->mock(UserInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->target->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(424242);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user2);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($user2, $user)
            ->once()
            ->andReturn(false);

        $attackers = [$this->targetWrapper];
        $this->fightLib->shouldReceive('getAttackers')
            ->with($this->targetWrapper)
            ->once()
            ->andReturn($attackers);

        $this->shipAttackCycle->shouldReceive('cycle')
            ->with($attackers, [$this->wrapper], true)
            ->once()
            ->andReturn($messages);

        $messages->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            "REASON"
        );

        $this->assertFalse($result);
    }

    public function testReactToThreatExpectTrueWhenFightHappened(): void
    {
        $user = $this->mock(UserInterface::class);
        $user2 = $this->mock(UserInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $user2->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->target->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(424242);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user2);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($user2, $user)
            ->once()
            ->andReturn(false);

        $attackers = [$this->targetWrapper];
        $this->fightLib->shouldReceive('getAttackers')
            ->with($this->targetWrapper)
            ->once()
            ->andReturn($attackers);

        $this->shipAttackCycle->shouldReceive('cycle')
            ->with($attackers, [$this->wrapper], true)
            ->once()
            ->andReturn($messages);

        $messages->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $messages->shouldReceive('getInformationDump')
            ->withNoArgs()
            ->once()
            ->andReturn($informations);

        $informations->shouldReceive('getInformationsAsString')
            ->withNoArgs()
            ->once()
            ->andReturn('INFOS');

        $this->game->shouldReceive('addInformationWrapper')
            ->with($informations)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                42,
                666,
                "CAUSE\nFolgende Aktionen wurden ausgeführt:\nINFOS",
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            )
            ->once()
            ->andReturn('INFOS');

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            "CAUSE"
        );

        $this->assertTrue($result);
    }
}
