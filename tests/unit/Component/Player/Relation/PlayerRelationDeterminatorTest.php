<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class PlayerRelationDeterminatorTest extends StuTestCase
{
    private MockInterface&FriendDeterminator $friendDeterminator;

    private MockInterface&EnemyDeterminator $enemyDeterminator;

    private PlayerRelationDeterminator $subject;

    private MockInterface&User $user;

    private MockInterface&User $opponent;

    #[Override]
    protected function setUp(): void
    {
        $this->friendDeterminator = $this->mock(FriendDeterminator::class);
        $this->enemyDeterminator = $this->mock(EnemyDeterminator::class);

        $this->subject = new PlayerRelationDeterminator(
            $this->friendDeterminator,
            $this->enemyDeterminator
        );

        $this->user = $this->mock(User::class);
        $this->opponent = $this->mock(User::class);
    }

    public function testIsFriendExpectTrueIfSameUser(): void
    {
        $this->assertTrue(
            $this->subject->isFriend($this->user, $this->user)
        );
    }

    public static function provideIsFriendData(): array
    {
        return [
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::USER, false],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::ALLY, false],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::NONE, true],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::ALLY, true],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::NONE, true],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::USER, false]
        ];
    }

    #[DataProvider('provideIsFriendData')]
    public function testIsFriend(
        PlayerRelationTypeEnum $friendRelation,
        PlayerRelationTypeEnum $enemyRelation,
        bool $expectedResult
    ): void {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($friendRelation);
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($enemyRelation);

        $this->assertEquals(
            $expectedResult,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public static function provideIsEnemyData(): array
    {
        return [
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::USER, true],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::ALLY, true],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::ALLY, false],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::USER, true]
        ];
    }

    #[DataProvider('provideIsEnemyData')]
    public function testIsEnemy(
        PlayerRelationTypeEnum $friendRelation,
        PlayerRelationTypeEnum $enemyRelation,
        bool $expectedResult
    ): void {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($friendRelation);
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($enemyRelation);

        $this->assertEquals(
            $expectedResult,
            $this->subject->isEnemy($this->user, $this->opponent)
        );
    }
}
