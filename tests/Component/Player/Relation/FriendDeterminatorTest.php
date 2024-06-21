<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\StuTestCase;

class FriendDeterminatorTest extends StuTestCase
{
    /** @var MockInterface&AllianceRelationRepositoryInterface */
    private MockInterface $allianceRelationRepository;

    /** @var MockInterface&ContactRepositoryInterface */
    private MockInterface $contactRepository;

    private FriendDeterminator $subject;

    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    /** @var MockInterface&UserInterface */
    private MockInterface $opponent;

    protected function setUp(): void
    {
        $this->allianceRelationRepository = $this->mock(AllianceRelationRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);

        $this->subject = new FriendDeterminator(
            $this->allianceRelationRepository,
            $this->contactRepository
        );

        $this->user = $this->mock(UserInterface::class);
        $this->opponent = $this->mock(UserInterface::class);
    }

    public function testIsFriendReturnsAllyIfAlliancesMatch(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $this->opponent->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $this->assertEquals(
            PlayerRelationTypeEnum::ALLY,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsFriendReturnsAllyIfAlliancesHaveFriendlyRelation(): void
    {
        $allianceUser = $this->mock(AllianceInterface::class);
        $allianceOpponent = $this->mock(AllianceInterface::class);

        $allianceUserId = 666;
        $allianceOpponentId = 42;

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceUser);

        $this->opponent->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceOpponent);

        $allianceUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceUserId);

        $allianceOpponent->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceOpponentId);

        $this->allianceRelationRepository->shouldReceive('getActiveByTypeAndAlliancePair')
            ->with(
                [
                    AllianceEnum::ALLIANCE_RELATION_FRIENDS,
                    AllianceEnum::ALLIANCE_RELATION_ALLIED,
                    AllianceEnum::ALLIANCE_RELATION_VASSAL
                ],
                $allianceOpponentId,
                $allianceUserId
            )
            ->once()
            ->andReturn($this->mock(AllianceRelationInterface::class));

        $this->assertEquals(
            PlayerRelationTypeEnum::ALLY,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsFriendReturnsNoneIfAlliancesHaveNoFriendlyRelationAndUserHasNoContact(): void
    {
        $allianceUser = $this->mock(AllianceInterface::class);
        $allianceOpponent = $this->mock(AllianceInterface::class);

        $allianceUserId = 666;
        $allianceOpponentId = 42;
        $userId = 33;
        $opponentId = 21;

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceUser);

        $this->opponent->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceOpponent);

        $allianceUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceUserId);

        $allianceOpponent->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceOpponentId);

        $this->allianceRelationRepository->shouldReceive('getActiveByTypeAndAlliancePair')
            ->with(
                [
                    AllianceEnum::ALLIANCE_RELATION_FRIENDS,
                    AllianceEnum::ALLIANCE_RELATION_ALLIED,
                    AllianceEnum::ALLIANCE_RELATION_VASSAL
                ],
                $allianceOpponentId,
                $allianceUserId
            )
            ->once()
            ->andReturnNull();

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->opponent->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($opponentId);

        $this->contactRepository->shouldReceive('getByUserAndOpponent')
            ->with($userId, $opponentId)
            ->once()
            ->andReturnNull();

        $this->assertEquals(
            PlayerRelationTypeEnum::NONE,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsFriendReturnsUserIfContactIsFriendly(): void
    {
        $userId = 33;
        $opponentId = 21;

        $contact = $this->mock(ContactInterface::class);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->opponent->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->opponent->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($opponentId);

        $this->contactRepository->shouldReceive('getByUserAndOpponent')
            ->with($userId, $opponentId)
            ->once()
            ->andReturn($contact);

        $contact->shouldReceive('isFriendly')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertEquals(
            PlayerRelationTypeEnum::USER,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }
}
