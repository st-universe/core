<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Doctrine\DBAL\Connection;
use Override;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Executes maintenance tasks like db backup and such
 */
class MaintenanceTickRunner implements TickRunnerInterface
{
    private const string TICK_DESCRIPTION = "maintenancetick";

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function __construct(
        private readonly GameConfigRepositoryInterface $gameConfigRepository,
        private readonly TransactionTickRunnerInterface $transactionTickRunner,
        private readonly Connection $connection,
        private readonly array $handlerList,
        private readonly UserRepositoryInterface $users,
        private readonly StuConfigInterface $config
    ) {}

    #[Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->setGameState(GameStateEnum::MAINTENANCE);

        try {
            $this->transactionTickRunner->runWithResetCheck(
                function (): void {
                    if (!$this->config->getDbSettings()->useSqlite()) {
                        $this->users->lockAllUsersForUpdate();
                    }

                    foreach ($this->handlerList as $handler) {
                        $handler->handle();
                    }
                },
                self::TICK_DESCRIPTION,
                $batchGroup,
                $batchGroupCount
            );
        } finally {
            $this->setGameState(GameStateEnum::ONLINE);
        }
    }

    private function setGameState(GameStateEnum $state): void
    {
        $this->gameConfigRepository->updateGameState($state, $this->connection);
    }
}
