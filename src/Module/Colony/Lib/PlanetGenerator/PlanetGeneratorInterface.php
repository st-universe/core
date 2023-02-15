<?php

namespace Stu\Module\Colony\Lib\PlanetGenerator;

interface PlanetGeneratorInterface
{
    /**
     * @return array{
     *     sizew: int,
     *     sizeh: int,
     *     0: array<mixed>,
     *     1: array<mixed>,
     *     2: array<mixed>,
     *     3: array<mixed>
     *  }
     */
    public function loadColonyClassConfig(int $planetTypeId): array;

    /**
     * @return array{surfaceWidth: int, surfaceFields: array<int, int>}
     */
    public function generateColony(
        int $planetTypeId,
        int $bonusFieldAmount
    ): array;
}
