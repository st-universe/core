<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Manager;

use Stu\Module\Tick\AbstractTickRunner;
use Stu\Module\Tick\TickManagerInterface;

/**
 * Executes the tick manager (stats refresh, etc...)
 */
final class TickManagerRunner extends AbstractTickRunner
{
    private TickManagerInterface $tickManager;

    public function __construct(
        TickManagerInterface $tickManager
    ) {
        $this->tickManager = $tickManager;
    }

    public function runInTransaction(int $batchGroup, int $batchGroupCount): void
    {
        $this->tickManager->work();
    }

    public function getTickDescription(): string
    {
        return "tickmanager";
    }
}
