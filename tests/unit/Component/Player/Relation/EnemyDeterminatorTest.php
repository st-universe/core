<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\StuTestCase;

class EnemyDeterminatorTest extends StuTestCase
{
    private MockInterface&RelationRepositoryInterface $relationRepository;
    private MockInterface&ContactRepositoryInterface $contactRepository;
    private EnemyDeterminator $subject;
    private MockInterface&User $user;
    private MockInterface&User $opponent;

    #[\Override]
    protected function setUp(): void
    {
        $this->relationRepository = $this->mock(RelationRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);
        $this->subject = new EnemyDeterminator($this->relationRepository, $this->contactRepository);
        $this->user = $this->mock(User::class);
        $this->opponent = $this->mock(User::class);
    }

    public function testIsEnemyReturnsNoneIfAlliancesMatch(): void
    {
        $alliance = $this->mock(Alliance::class);
        $this->user->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $this->opponent->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $alliance->shouldReceive('getId')->andReturn(123);

        $this->assertSame(PlayerRelationTypeEnum::NONE, $this->subject->isEnemy(
            $this->user,
            $this->opponent
        ));
    }

    public function testIsEnemyReturnsAllyIfPartiesHaveWarRelation(): void
    {
        $alliance = $this->mock(Alliance::class);
        $otherAlliance = $this->mock(Alliance::class);
        $this->user->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $this->opponent->shouldReceive('getAlliance')->once()->andReturn($otherAlliance);
        $alliance->shouldReceive('getId')->andReturn(666);
        $otherAlliance->shouldReceive('getId')->andReturn(42);
        $this->relationRepository
            ->shouldReceive('getActiveByParties')
            ->with(
                [AllianceRelationTypeEnum::WAR->value],
                $alliance,
                $otherAlliance
            )
            ->once()
            ->andReturn($this->mock(Relation::class));

        $this->assertSame(PlayerRelationTypeEnum::ALLY, $this->subject->isEnemy(
            $this->user,
            $this->opponent
        ));
    }

    public function testIsEnemyChecksContactAfterMissingWarRelation(): void
    {
        $this->user->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->opponent->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->user->shouldReceive('getId')->andReturn(33);
        $this->opponent->shouldReceive('getId')->andReturn(21);
        $this->relationRepository
            ->shouldReceive('getActiveByParties')
            ->with(
                [AllianceRelationTypeEnum::WAR->value],
                $this->user,
                $this->opponent
            )
            ->once()
            ->andReturnNull();
        $this->contactRepository
            ->shouldReceive('getByUserAndOpponent')
            ->with(33, 21)
            ->once()
            ->andReturnNull();

        $this->assertSame(PlayerRelationTypeEnum::NONE, $this->subject->isEnemy(
            $this->user,
            $this->opponent
        ));
    }

    public function testIsEnemyReturnsUserIfContactIsEnemy(): void
    {
        $contact = $this->mock(Contact::class);
        $this->user->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->opponent->shouldReceive('getAlliance')->once()->andReturnNull();
        $this->user->shouldReceive('getId')->andReturn(33);
        $this->opponent->shouldReceive('getId')->andReturn(21);
        $this->relationRepository
            ->shouldReceive('getActiveByParties')
            ->with(
                [AllianceRelationTypeEnum::WAR->value],
                $this->user,
                $this->opponent
            )
            ->once()
            ->andReturnNull();
        $this->contactRepository
            ->shouldReceive('getByUserAndOpponent')
            ->with(33, 21)
            ->once()
            ->andReturn($contact);
        $contact->shouldReceive('isEnemy')->once()->andReturnTrue();

        $this->assertSame(PlayerRelationTypeEnum::USER, $this->subject->isEnemy(
            $this->user,
            $this->opponent
        ));
    }
}
