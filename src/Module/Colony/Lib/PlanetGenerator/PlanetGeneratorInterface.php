<?php

namespace Stu\Module\Colony\Lib\PlanetGenerator;

use Stu\Orm\Entity\ColonyInterface;

interface PlanetGeneratorInterface
{
    public function loadPlanetTypeConfig(int $colonyClass): array;

    public function generateColony(ColonyInterface $colony): array;
}
