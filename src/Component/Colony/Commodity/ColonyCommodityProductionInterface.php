<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Lib\ColonyProduction\ColonyProduction;

interface ColonyCommodityProductionInterface
{
    /**
     * @return array<int, ColonyProduction>
     */
    public function getProduction(): array;
}