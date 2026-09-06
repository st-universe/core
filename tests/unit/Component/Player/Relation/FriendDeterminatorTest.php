<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\StuTestCase;

class FriendDeterminatorTest extends StuTestCase
{
    private MockInterface&RelationRepositoryInterface $relationRepository;
    private MockInterface&ContactRepositoryInterface $contactRepository;
    private FriendDeterminator $subject;
    private MockInterface&User $user;
    private MockInterface&User $opponent;

    #[\Override]
    protected function setUp(): void
    {
        $this->relationRepository = $this->mock(RelationRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);
        $this->subject = new FriendDeterminator($this->relationRepository, $this->contactRepository);
        $this->user = $this->mock(User::class);
        $this->opponent = $this->mock(User::class);
    }

    public function testIsFriendReturnsAllyIfAlliancesMatch(): void
    {
        $alliance = $this->mock(Alliance::class);
        $this->user->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $this->opponent->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $alliance->shouldReceive('getId')->andReturn(424242);

        $this->assertSame(PlayerRelationTypeEnum::ALLY, $this->subject->isFriend(
            $this->user,
            $this->opponent
        ));
    }

    public function testIsFriendReturnsAllyIfRelationGrantsPermission(): void
    {
        $alliance = $this->mock(Alliance::class);
        $otherAlliance = $this->mock(Alliance::class);
        $relation = $this->mock(Relation::class);
        $this->user->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $this->opponent->shouldReceive('getAlliance')->once()->andReturn($otherAlliance);
        $alliance->shouldReceive('getId')->andReturn(666);
        $otherAlliance->shouldReceive('getId')->andReturn(42);
        $this->relationRepository
            ->shouldReceive('getActiveByParties')
            ->with([], $alliance, $otherAlliance)
            ->once()
            ->andReturn($relation);
        $relation
            ->shouldReceive('hasPermissionFor')
            ->with($this->user, RelationPermissionEnum::FRIENDLY)
            ->once()
            ->andReturnTrue();

        $this->assertSame(PlayerRelationTypeEnum::ALLY, $this->subject->isFriend(
            $this->user,
            $this->opponent
        ));
    }

    public function testIsFriendChecksContactAfterMissingRelationPermission(): void
    {
        $userId = 33;
        $opponentId = 21;
        $this->user->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->opponent->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->user->shouldReceive('getId')->andReturn($userId);
        $this->opponent->shouldReceive('getId')->andReturn($opponentId);
        $this->relationRepository
            ->shouldReceive('getActiveByParties')
            ->with([], $this->user, $this->opponent)
            ->once()
            ->andReturnNull();
        $this->contactRepository
            ->shouldReceive('getByUserAndOpponent')
            ->with($userId, $opponentId)
            ->once()
            ->andReturnNull();

        $this->assertSame(PlayerRelationTypeEnum::NONE, $this->subject->isFriend(
            $this->user,
            $this->opponent
        ));
    }

    public function testIsFriendReturnsUserIfContactIsFriendly(): void
    {
        $contact = $this->mock(Contact::class);
        $this->user->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->opponent->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->user->shouldReceive('getId')->andReturn(33);
        $this->opponent->shouldReceive('getId')->andReturn(21);
        $this->relationRepository
            ->shouldReceive('getActiveByParties')
            ->with([], $this->user, $this->opponent)
            ->once()
            ->andReturnNull();
        $this->contactRepository
            ->shouldReceive('getByUserAndOpponent')
            ->with(33, 21)
            ->once()
            ->andReturn($contact);
        $contact->shouldReceive('isFriendly')->once()->andReturnTrue();

        $this->assertSame(PlayerRelationTypeEnum::USER, $this->subject->isFriend(
            $this->user,
            $this->opponent
        ));
    }
}
