<?php

namespace Stu\Module\Control;

use Ubench;

interface BenchmarkResultInterface
{
    /**
     * @return array{executionTime: float|string, memoryPeakUsage: float|string}
     */
    public function getResult(Ubench $benchmark): array;
}
