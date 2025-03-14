<?php

namespace Stu\Module\Tick\Maintenance;

use Doctrine\DBAL\Connection;
use Override;
use Stu\Component\Game\GameEnum;
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
    ) {
    }

    #[Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE);

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

        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);
    }

    private function setGameState(int $stateId): void
    {
        $this->gameConfigRepository->updateGameState($stateId, $this->connection);
    }
}
