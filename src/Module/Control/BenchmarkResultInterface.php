<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface BenchmarkResultInterface
{
    /**
     * @return array{executionTime: float|string, memoryPeakUsage: float|string}
     */
    public function getResult(): array;
}
