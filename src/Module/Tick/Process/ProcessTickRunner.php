<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameStateInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Executes all process related tasks (e.g. finishing build processes, ...)
 */
class ProcessTickRunner implements TickRunnerInterface
{
    private const string TICK_DESCRIPTION = "processtick";

    /**
     * @param array<ProcessTickHandlerInterface> $handlerList
     */
    public function __construct(
        private TransactionTickRunnerInterface $transactionTickRunner,
        private array $handlerList,
        private UserRepositoryInterface $users,
        private StuConfigInterface $config,
        private GameStateInterface $gameState
    ) {}

    #[Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        if ($this->gameState->getGameState() === GameStateEnum::MAINTENANCE) {
            return;
        }

        $this->transactionTickRunner->runWithResetCheck(
            function (): void {
                if (!$this->config->getDbSettings()->useSqlite()) {
                    $this->users->lockAllUsersForUpdate();
                }
                foreach ($this->handlerList as $process) {
                    $process->work();
                }
            },
            self::TICK_DESCRIPTION,
            $batchGroup,
            $batchGroupCount
        );
    }
}
