<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ProcessTickRunnerTest extends StuTestCase
{
    private MockInterface&TransactionTickRunnerInterface $transactionTickRunner;

    private MockInterface&ProcessTickHandlerInterface $handler;

    private MockInterface&UserRepositoryInterface $users;

    private MockInterface&StuConfigInterface $config;

    private ProcessTickRunner $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);

        $this->handler = $this->mock(ProcessTickHandlerInterface::class);
        $this->users = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);

        $this->subject = new ProcessTickRunner(
            $this->transactionTickRunner,
            [
                $this->handler
            ],
            $this->users,
            $this->config
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
