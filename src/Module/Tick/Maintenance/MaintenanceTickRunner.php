<?php

namespace Stu\Module\Tick\Maintenance;

use Doctrine\DBAL\Connection;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

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
        private readonly array $handlerList
    ) {}

    #[\Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->setGameState(GameStateEnum::MAINTENANCE);

        $this->transactionTickRunner->runWithResetCheck(
            function (): void {
                foreach ($this->handlerList as $handler) {
                    $handler->handle();
                }
            },
            self::TICK_DESCRIPTION,
            $batchGroup,
            $batchGroupCount
        );

        $this->setGameState(GameStateEnum::ONLINE);
    }

    private function setGameState(GameStateEnum $state): void
    {
        $this->gameConfigRepository->updateGameState($state, $this->connection);
    }
}
