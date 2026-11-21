<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Mockery;
use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameStateInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ProcessTickRunnerTest extends StuTestCase
{
    private MockInterface&TransactionTickRunnerInterface $transactionTickRunner;

    private MockInterface&ProcessTickHandlerInterface $handler;

    private MockInterface&UserRepositoryInterface $users;

    private MockInterface&StuConfigInterface $config;

    private MockInterface&GameStateInterface $gameState;

    private ProcessTickRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);

        $this->handler = $this->mock(ProcessTickHandlerInterface::class);
        $this->users = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);
        $this->gameState = $this->mock(GameStateInterface::class);
        $this->gameState->shouldReceive('getGameState')->byDefault()->andReturn(GameStateEnum::ONLINE);

        $this->subject = new ProcessTickRunner(
            $this->transactionTickRunner,
            [
                $this->handler
            ],
            $this->users,
            $this->config,
            $this->gameState
        );
    }

    public function testRunRuns(): void
    {
        $batchGroup = 2;
        $batchGroupCount = 5;

        $this->config->shouldReceive('getDbSettings->useSqlite')->once()->andReturn(false);
        $this->users->shouldReceive('lockAllUsersForUpdate')->withNoArgs()->once()->globally()->ordered();

        $this->handler->shouldReceive('work')
            ->withNoArgs()
            ->once()->globally()->ordered();

        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(
                Mockery::on(function ($callable): bool {
                    if (!is_callable($callable)) {
                        return false;
                    }
                    $callable();
                    return true;
                }),
                "processtick",
                $batchGroup,
                $batchGroupCount
            )
            ->once();


        $this->subject->run($batchGroup, $batchGroupCount);
    }

    public function testSqliteExecutesHandlersWithoutRowLocks(): void
    {
        $this->config->shouldReceive('getDbSettings->useSqlite')->once()->andReturn(true);
        $this->users->shouldNotReceive('lockAllUsersForUpdate');
        $this->handler->shouldReceive('work')->withNoArgs()->once();
        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(Mockery::type('callable'), 'processtick', 1, 1)->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        $this->subject->run(1, 1);
    }

    public function testResetCheckCanSkipTheEntireProcessIncludingLocks(): void
    {
        $this->config->shouldNotReceive('getDbSettings');
        $this->users->shouldNotReceive('lockAllUsersForUpdate');
        $this->handler->shouldNotReceive('work');
        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(Mockery::type('callable'), 'processtick', 1, 1)->once();

        $this->subject->run(1, 1);
    }

    public function testOnlyTheFirstRunAfterMaintenanceOpensATransaction(): void
    {
        $states = array_fill(0, 5, GameStateEnum::MAINTENANCE);
        $states[] = GameStateEnum::ONLINE;
        $this->gameState->shouldReceive('getGameState')->times(6)->andReturn(...$states);
        $this->config->shouldReceive('getDbSettings->useSqlite')->once()->andReturn(false);
        $this->users->shouldReceive('lockAllUsersForUpdate')->once()->globally()->ordered();
        $this->handler->shouldReceive('work')->once()->globally()->ordered();
        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(Mockery::type('callable'), 'processtick', 1, 1)->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        for ($minute = 0; $minute < 6; $minute++) {
            $this->subject->run(1, 1);
        }
    }

    public function testLockFailurePreventsHandlersFromTakingOtherLocks(): void
    {
        $this->config->shouldReceive('getDbSettings->useSqlite')->once()->andReturn(false);
        $this->users->shouldReceive('lockAllUsersForUpdate')->withNoArgs()->once()
            ->andThrow(new RuntimeException('Sperre fehlgeschlagen'));
        $this->handler->shouldNotReceive('work');
        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(Mockery::type('callable'), 'processtick', 1, 1)->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Sperre fehlgeschlagen');

        $this->subject->run(1, 1);
    }
}
