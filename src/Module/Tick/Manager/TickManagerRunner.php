<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Manager;

use Stu\Module\Tick\TickManagerInterface;
use Stu\Module\Tick\TickRunnerInterface;

/**
 * Executes the tick manager (stats refresh, etc...)
 */
final class TickManagerRunner implements TickRunnerInterface
{
    public function __construct(private TickManagerInterface $tickManager)
    {
    }

    #[\Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->tickManager->work();
    }

    public function getTickDescription(): string
    {
        return "tickmanager";
    }
}
