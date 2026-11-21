<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Doctrine\DBAL\Connection;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class MaintenanceTickRunnerTest extends StuTestCase
{
    private MockInterface&GameConfigRepositoryInterface $gameConfigRepository;

    private MockInterface&TransactionTickRunnerInterface $transactionTickRunner;

    private MockInterface&Connection $connection;

    private MockInterface&MaintenanceHandlerInterface $maintenanceHandler;

    private MockInterface&UserRepositoryInterface $users;

    private MockInterface&StuConfigInterface $config;

    private MaintenanceTickRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);
        $this->connection = $this->mock(Connection::class);

        $this->maintenanceHandler = $this->mock(MaintenanceHandlerInterface::class);
        $this->users = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);

        $this->subject = new MaintenanceTickRunner(
            $this->gameConfigRepository,
            $this->transactionTickRunner,
            $this->connection,
            [
                $this->maintenanceHandler,
            ],
            $this->users,
            $this->config
        );
    }

    public function testRunRestoresOnlineStateAndPropagatesAnError(): void
    {
        $errorMessage = 'some-error';
        $error = new Exception($errorMessage);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorMessage);

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameStateEnum::MAINTENANCE, $this->connection)
            ->once()->globally()->ordered();

        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->once()->globally()->ordered()
            ->andThrow($error);

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameStateEnum::ONLINE, $this->connection)
            ->once()->globally()->ordered();

        $this->subject->run(1, 1);
    }

    public function testRunRuns(): void
    {
        $batchGroup = 2;
        $batchGroupCount = 5;

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameStateEnum::MAINTENANCE, $this->connection)
            ->once();
        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameStateEnum::ONLINE, $this->connection)
            ->once();

        $this->config->shouldReceive('getDbSettings->useSqlite')->once()->andReturn(false);
        $this->users->shouldReceive('lockAllUsersForUpdate')->withNoArgs()->once()->globally()->ordered();

        $this->maintenanceHandler->shouldReceive('handle')
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
                "maintenancetick",
                $batchGroup,
                $batchGroupCount
            )
            ->once();

        $this->subject->run($batchGroup, $batchGroupCount);
    }

    public function testSqliteExecutesHandlersWithoutRowLocks(): void
    {
        $this->gameConfigRepository->shouldReceive('updateGameState')->with(GameStateEnum::MAINTENANCE, $this->connection)->once();
        $this->gameConfigRepository->shouldReceive('updateGameState')->with(GameStateEnum::ONLINE, $this->connection)->once();
        $this->config->shouldReceive('getDbSettings->useSqlite')->once()->andReturn(true);
        $this->users->shouldNotReceive('lockAllUsersForUpdate');
        $this->maintenanceHandler->shouldReceive('handle')->withNoArgs()->once();
        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(Mockery::type('callable'), 'maintenancetick', 1, 1)->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        $this->subject->run(1, 1);
    }
}
