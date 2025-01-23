<?php

namespace Stu\Module\Control;

use Ubench;

class BenchmarkResult implements BenchmarkResultInterface
{
    public function getResult(Ubench $benchmark): array
    {
        return [
            'executionTime' => $benchmark->getTime(),
            'memoryPeakUsage' => $benchmark->getMemoryPeak()
        ];
    }
}
