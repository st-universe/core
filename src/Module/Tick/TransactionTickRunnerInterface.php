<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Throwable;

interface TransactionTickRunnerInterface
{
    /**
     * Runs the tick
     *
     * @throws Throwable
     */
    public function runWithResetCheck(
        callable $fn,
        string $tickDescription,
        int $batchGroup,
        int $batchGroupCount
    ): void;

    public function isGameStateReset(): bool;
}
