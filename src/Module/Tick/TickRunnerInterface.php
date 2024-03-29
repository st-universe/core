<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Throwable;

interface TickRunnerInterface
{
    /**
     * Runs the tick
     *
     * @throws Throwable
     */
    public function run(int $batchGroup, int $batchGroupCount): void;
}
