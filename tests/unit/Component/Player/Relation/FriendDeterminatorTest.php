<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Override;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\StuTestCase;

class FriendDeterminatorTest extends StuTestCase
{
    private MockInterface&AllianceRelationRepositoryInterface $allianceRelationRepository;
    private MockInterface&ContactRepositoryInterface $contactRepository;

    private FriendDeterminator $subject;

    private MockInterface&User $user;

    private MockInterface&User $opponent;

    #[Override]
    protected function setUp(): void
    {
        $this->allianceRelationRepository = $this->mock(AllianceRelationRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);

        $this->subject = new FriendDeterminator(
            $this->allianceRelationRepository,
            $this->contactRepository
        );

        $this->user = $this->mock(User::class);
        $this->opponent = $this->mock(User::class);
    }

    public function testIsFriendReturnsAllyIfAlliancesMatch(): void
    {
        $alliance = $this->mock(Alliance::class);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(424242);

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
        $allianceUser = $this->mock(Alliance::class);
        $allianceOpponent = $this->mock(Alliance::class);

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
            ->andReturn($allianceUserId);

        $allianceOpponent->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($allianceOpponentId);

        $this->allianceRelationRepository->shouldReceive('getActiveByTypeAndAlliancePair')
            ->with(
                [
                    AllianceRelationTypeEnum::FRIENDS->value,
                    AllianceRelationTypeEnum::ALLIED->value,
                    AllianceRelationTypeEnum::VASSAL->value
                ],
                $allianceOpponentId,
                $allianceUserId
            )
            ->once()
            ->andReturn($this->mock(AllianceRelation::class));

        $this->assertEquals(
            PlayerRelationTypeEnum::ALLY,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsFriendReturnsNoneIfAlliancesHaveNoFriendlyRelationAndUserHasNoContact(): void
    {
        $allianceUser = $this->mock(Alliance::class);
        $allianceOpponent = $this->mock(Alliance::class);

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
            ->andReturn($allianceUserId);

        $allianceOpponent->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($allianceOpponentId);

        $this->allianceRelationRepository->shouldReceive('getActiveByTypeAndAlliancePair')
            ->with(
                [
                    AllianceRelationTypeEnum::FRIENDS->value,
                    AllianceRelationTypeEnum::ALLIED->value,
                    AllianceRelationTypeEnum::VASSAL->value
                ],
                $allianceOpponentId,
                $allianceUserId
            )
            ->once()
            ->andReturnNull();

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($userId);

        $this->opponent->shouldReceive('getId')
            ->withNoArgs()
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

        $contact = $this->mock(Contact::class);

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
            ->andReturn($userId);

        $this->opponent->shouldReceive('getId')
            ->withNoArgs()
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
