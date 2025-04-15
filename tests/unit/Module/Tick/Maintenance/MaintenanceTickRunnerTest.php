<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Doctrine\DBAL\Connection;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\StuTestCase;

class MaintenanceTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&GameConfigRepositoryInterface */
    private MockInterface $gameConfigRepository;

    /** @var MockInterface&TransactionTickRunnerInterface */
    private MockInterface $transactionTickRunner;

    /** @var MockInterface&Connection */
    private MockInterface $connection;

    /** @var MockInterface&MaintenanceHandlerInterface */
    private MockInterface $maintenanceHandler;

    private MaintenanceTickRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);
        $this->connection = $this->mock(Connection::class);

        $this->maintenanceHandler = $this->mock(MaintenanceHandlerInterface::class);

        $this->subject = new MaintenanceTickRunner(
            $this->gameConfigRepository,
            $this->transactionTickRunner,
            $this->connection,
            [
                $this->maintenanceHandler,
            ]
        );
    }

    public function testRunErrorsOnInternalError(): void
    {
        $errorMessage = 'some-error';
        $error = new Exception($errorMessage);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorMessage);

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE, $this->connection)
            ->once();

        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->once()
            ->andThrow($error);

        $this->subject->run(1, 1);
    }

    public function testRunRuns(): void
    {
        $batchGroup = 2;
        $batchGroupCount = 5;

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE, $this->connection)
            ->once();
        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE, $this->connection)
            ->once();

        $this->maintenanceHandler->shouldReceive('handle')
            ->withNoArgs()
            ->once();

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
}
