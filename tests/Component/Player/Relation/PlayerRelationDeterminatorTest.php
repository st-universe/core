<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class PlayerRelationDeterminatorTest extends StuTestCase
{
    /** @var MockInterface&FriendDeterminator */
    private $friendDeterminator;

    /** @var MockInterface&EnemyDeterminator */
    private $enemyDeterminator;

    private PlayerRelationDeterminator $subject;

    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    /** @var MockInterface&UserInterface */
    private MockInterface $opponent;

    protected function setUp(): void
    {
        $this->friendDeterminator = $this->mock(FriendDeterminator::class);
        $this->enemyDeterminator = $this->mock(EnemyDeterminator::class);

        $this->subject = new PlayerRelationDeterminator(
            $this->friendDeterminator,
            $this->enemyDeterminator
        );

        $this->user = $this->mock(UserInterface::class);
        $this->opponent = $this->mock(UserInterface::class);
    }

    public function testIsFriendExpectFalseWhenNotFriend(): void
    {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(false);

        static::assertFalse(
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsFriendExpectFalseWhenFriendButEnemy(): void
    {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(true);
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(true);

        static::assertFalse(
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsFriendExpectTrueWhenFriendAndNotEnemy(): void
    {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(true);
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(false);

        static::assertTrue(
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public function testIsEnemyExpectFalseWhenNotEnemy(): void
    {
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(false);

        static::assertFalse(
            $this->subject->isEnemy($this->user, $this->opponent)
        );
    }

    public function testIsEnemyExpectFalseWhenEnemyButFriend(): void
    {
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(true);
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(true);

        static::assertFalse(
            $this->subject->isEnemy($this->user, $this->opponent)
        );
    }

    public function testIsEnemyExpectTrueWhenEnemyAndNotFriend(): void
    {
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(true);
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->once()
            ->andReturn(false);

        static::assertTrue(
            $this->subject->isEnemy($this->user, $this->opponent)
        );
    }
}
