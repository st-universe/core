<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Mockery\MockInterface;
use Override;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\SingletonBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ThreatReactionTest extends StuTestCase
{
    /** @var MockInterface&PlayerRelationDeterminatorInterface */
    private $playerRelationDeterminator;

    /** @var MockInterface&SpacecraftAttackCycleInterface */
    private $spacecraftAttackCycle;

    /** @var MockInterface&BattlePartyFactoryInterface */
    private $battlePartyFactory;

    /** @var MockInterface&PrivateMessageSenderInterface */
    private $privateMessageSender;

    /** @var MockInterface&GameControllerInterface */
    private $game;

    /** @var MockInterface&ShipInterface */
    private $ship;
    /** @var MockInterface&ShipInterface */
    private $target;

    /** @var MockInterface&ShipWrapperInterface */
    private $wrapper;
    /** @var MockInterface&ShipWrapperInterface */
    private $targetWrapper;

    private ThreatReactionInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->playerRelationDeterminator = $this->mock(PlayerRelationDeterminatorInterface::class);
        $this->spacecraftAttackCycle = $this->mock(SpacecraftAttackCycleInterface::class);
        $this->battlePartyFactory = $this->mock(BattlePartyFactoryInterface::class);
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
            $this->spacecraftAttackCycle,
            $this->battlePartyFactory,
            $this->privateMessageSender,
            $this->game
        );
    }

    public function testReactToThreatExpectFalseWhenTargetAlertGreen(): void
    {
        $this->targetWrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            ShipInteractionEnum::BOARD_SHIP
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

        $this->targetWrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            ShipInteractionEnum::BOARD_SHIP
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

        $this->targetWrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
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
            ShipInteractionEnum::BOARD_SHIP
        );

        $this->assertFalse($result);
    }

    public function testReactToThreatExpectFalseWhenEmptyAttackCycle(): void
    {
        $user = $this->mock(UserInterface::class);
        $user2 = $this->mock(UserInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $attackingBattleParty = $this->mock(AttackingBattleParty::class);
        $attackedBattleParty = $this->mock(SingletonBattleParty::class);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn("SECTOR");

        $this->targetWrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user2);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("TARGET");

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($user2, $user)
            ->once()
            ->andReturn(false);

        $this->battlePartyFactory->shouldReceive('createAttackingBattleParty')
            ->with($this->targetWrapper, false)
            ->once()
            ->andReturn($attackingBattleParty);
        $this->battlePartyFactory->shouldReceive('createSingletonBattleParty')
            ->with($this->wrapper)
            ->once()
            ->andReturn($attackedBattleParty);

        $this->spacecraftAttackCycle->shouldReceive('cycle')
            ->with($attackingBattleParty, $attackedBattleParty, SpacecraftAttackCauseEnum::BOARD_SHIP)
            ->once()
            ->andReturn($messages);

        $messages->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            ShipInteractionEnum::BOARD_SHIP
        );

        $this->assertFalse($result);
    }

    public function testReactToThreatExpectTrueWhenFightHappened(): void
    {
        $user = $this->mock(UserInterface::class);
        $user2 = $this->mock(UserInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $attackingBattleParty = $this->mock(AttackingBattleParty::class);
        $attackedBattleParty = $this->mock(SingletonBattleParty::class);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn("SECTOR");

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $user2->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->targetWrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user2);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("TARGET");

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($user2, $user)
            ->once()
            ->andReturn(false);

        $this->battlePartyFactory->shouldReceive('createAttackingBattleParty')
            ->with($this->targetWrapper, false)
            ->once()
            ->andReturn($attackingBattleParty);
        $this->battlePartyFactory->shouldReceive('createSingletonBattleParty')
            ->with($this->wrapper)
            ->once()
            ->andReturn($attackedBattleParty);

        $this->spacecraftAttackCycle->shouldReceive('cycle')
            ->with($attackingBattleParty, $attackedBattleParty, SpacecraftAttackCauseEnum::BOARD_SHIP)
            ->once()
            ->andReturn($messages);

        $attackingBattleParty->shouldReceive('getPrivateMessageType')
            ->withNoArgs()
            ->once()
            ->andReturn(PrivateMessageFolderTypeEnum::SPECIAL_SHIP);

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
                "Die SHIP versucht die TARGET in Sektor SECTOR zu entern.\nFolgende Aktionen wurden ausgeführt:\nINFOS",
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            )
            ->once()
            ->andReturn('INFOS');

        $result = $this->subject->reactToThreat(
            $this->wrapper,
            $this->targetWrapper,
            ShipInteractionEnum::BOARD_SHIP
        );

        $this->assertTrue($result);
    }
}
