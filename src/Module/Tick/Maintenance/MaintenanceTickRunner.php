<?php

namespace Stu\Module\Tick\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

/**
 * Executes maintenance tasks like db backup and such
 */
final class MaintenanceTickRunner implements TickRunnerInterface
{
    private const TICK_DESCRIPTION = "maintenancetick";

    private GameConfigRepositoryInterface $gameConfigRepository;

    private TransactionTickRunnerInterface $transactionTickRunner;

    /** @var array<MaintenanceHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        TransactionTickRunnerInterface $transactionTickRunner,
        array $handlerList
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->transactionTickRunner = $transactionTickRunner;
        $this->handlerList = $handlerList;
    }

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
        $this->gameConfigRepository->updateGameState($stateId);
    }
}
