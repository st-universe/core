<?php

namespace Stu\Module\Colony\Lib\PlanetGenerator;

interface PlanetGeneratorInterface
{
    public function generateColony(int $id, int $bonusfields = 2): array;
}
