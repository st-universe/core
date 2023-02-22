<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Lib\ColonyProduction\ColonyProduction;

interface ColonyProductionSumReducerInterface
{
    /**
     * @param array<int, ColonyProduction> $production
     */
    public function reduce(array $production): int;
}
