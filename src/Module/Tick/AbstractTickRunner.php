<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Stu\Module\Logging\LoggerUtilInterface;
use Ubench;

abstract class AbstractTickRunner implements TickRunnerInterface
{
    protected abstract function getBenchmark(): Ubench;
    protected abstract function getLoggerUtil(): LoggerUtilInterface;

    protected function logBenchmarkResult(): void
    {
        $this->getBenchmark()->end();

        $this->getLoggerUtil()->log(sprintf(
            'benchmarkResult - executionTime: %s, memoryUsage: %s, memoryPeakUsage: %s',
            $this->getBenchmark()->getTime(),
            $this->getBenchmark()->getMemoryUsage(),
            $this->getBenchmark()->getMemoryPeak()
        ));
    }
}
