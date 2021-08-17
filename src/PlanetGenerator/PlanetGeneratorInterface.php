<?php

namespace Stu\PlanetGenerator;

interface PlanetGeneratorInterface
{
    public function generateColony(int $id, int $bonusfields = 2): array;
}