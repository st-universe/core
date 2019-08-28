<?php

namespace Stu\Lib\PlanetGenerator;

interface PlanetGeneratorInterface
{
    public function generateColony(int $id, int $bonusfields = 2): array;
}