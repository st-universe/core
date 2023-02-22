<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Stu\Module\Logging\LoggerUtilInterface;
use Ubench;

abstract class AbstractTickManager
{
    protected abstract function getBenchmark(): Ubench;
    protected abstract function getLoggerUtil(): LoggerUtilInterface;

    protected function logBenchmarkResult(int $entityCount): void
    {
        $this->getBenchmark()->end();

        $this->getLoggerUtil()->log(sprintf(
            'benchmarkResult for %d entities - executionTime: %s, memoryPeakUsage: %s',
            $entityCount,
            $this->getBenchmark()->getTime(),
            $this->getBenchmark()->getMemoryPeak()
        ));
    }
}
