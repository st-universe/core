<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use BadMethodCallException;
use Mockery\MockInterface;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\StuTestCase;

final class GameTurnProviderTest extends StuTestCase
{
    private MockInterface&GameTurnRepositoryInterface $gameTurnRepository;

    private GameTurnProvider $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $this->subject = new GameTurnProvider($this->gameTurnRepository);
    }

    public function testGetCurrentRoundReturnsCurrentRound(): void
    {
        $currentRound = $this->mock(GameTurn::class);

        $this->gameTurnRepository->shouldReceive('getCurrent')
            ->withNoArgs()
            ->once()
            ->andReturn($currentRound);

        static::assertSame($currentRound, $this->subject->getCurrentRound());
    }

    public function testGetCurrentRoundReturnsCachedCurrentRound(): void
    {
        $currentRound = $this->mock(GameTurn::class);

        $this->gameTurnRepository->shouldReceive('getCurrent')
            ->withNoArgs()
            ->once()
            ->andReturn($currentRound);

        $firstResult = $this->subject->getCurrentRound();
        $secondResult = $this->subject->getCurrentRound();

        static::assertSame($firstResult, $secondResult);
    }

    public function testGetCurrentRoundThrowsWhenNoCurrentRoundExists(): void
    {
        static::expectException(BadMethodCallException::class);
        static::expectExceptionMessage('no current round existing');

        $this->gameTurnRepository->shouldReceive('getCurrent')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->getCurrentRound();
    }
}
