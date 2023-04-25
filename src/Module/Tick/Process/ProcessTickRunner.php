<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;

/**
 * Executes all process related tasks (e.g. finishing build processes, ...)
 */
final class ProcessTickRunner implements TickRunnerInterface
{
    private const TICK_DESCRIPTION = "processtick";

    private TransactionTickRunnerInterface $transactionTickRunner;

    /** @var array<ProcessTickHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<ProcessTickHandlerInterface> $handlerList
     */
    public function __construct(
        TransactionTickRunnerInterface $transactionTickRunner,
        array $handlerList
    ) {
        $this->transactionTickRunner = $transactionTickRunner;
        $this->handlerList = $handlerList;
    }

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
