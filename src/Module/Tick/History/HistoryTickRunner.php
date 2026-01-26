<?php

declare(strict_types=1);

namespace Stu\Module\Tick\History;

use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;

/**
 * Executes the history tick
 */
final class HistoryTickRunner implements TickRunnerInterface
{
    private const string TICK_DESCRIPTION = "historytick";

    /**
     * @param array<HistoryTickHandlerInterface> $handlerList
     */
    public function __construct(
        private array $handlerList,
        private TransactionTickRunnerInterface $transactionTickRunner,
    ) {
    }

    #[\Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        if ($this->transactionTickRunner->isGameStateReset()) {
            return;
        }

        StuLogger::log("Starting History Tick", LogTypeEnum::TICK);

        $this->transactionTickRunner->runWithResetCheck(
            function (): void {
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
