<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Ubench;

abstract class AbstractTickManager
{
    abstract protected function getBenchmark(): Ubench;

    protected function logBenchmarkResult(int $entityCount): void
    {
        $this->getBenchmark()->end();

        StuLogger::log(sprintf(
            'benchmarkResult for %d entities - executionTime: %s, memoryPeakUsage: %s',
            $entityCount,
            $this->getBenchmark()->getTime(),
            $this->getBenchmark()->getMemoryPeak()
        ), LogTypeEnum::TICK);
    }
}
