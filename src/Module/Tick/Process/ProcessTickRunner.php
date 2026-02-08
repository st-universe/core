<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;

/**
 * Executes all process related tasks (e.g. finishing build processes, ...)
 */
class ProcessTickRunner implements TickRunnerInterface
{
    private const string TICK_DESCRIPTION = "processtick";

    /**
     * @param array<ProcessTickHandlerInterface> $handlerList
     */
    public function __construct(private TransactionTickRunnerInterface $transactionTickRunner, private array $handlerList) {}

    #[\Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
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
