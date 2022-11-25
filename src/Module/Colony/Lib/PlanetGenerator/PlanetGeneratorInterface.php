<?php

namespace Stu\Module\Colony\Lib\PlanetGenerator;

use Stu\Orm\Entity\ColonyInterface;

interface PlanetGeneratorInterface
{
    public function loadColonyClassConfig(int $colonyClassId): array;

    public function generateColony(ColonyInterface $colony): array;
}
