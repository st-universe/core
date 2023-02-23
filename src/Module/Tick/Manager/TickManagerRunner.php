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
    private TickManagerInterface $tickManager;

    public function __construct(
        TickManagerInterface $tickManager
    ) {
        $this->tickManager = $tickManager;
    }

    public function run(): void
    {
        $this->tickManager->work();
    }
}
