<?php

namespace Stu\Module\Control;

use Ubench;

class BenchmarkResult implements BenchmarkResultInterface
{
    public function __construct(
        private readonly Ubench $benchmark
    ) {}

    #[\Override]
    public function getResult(): array
    {
        $this->benchmark->end();

        return [
            'executionTime' => $this->benchmark->getTime(),
            'memoryPeakUsage' => $this->benchmark->getMemoryPeak()
        ];
    }
}
