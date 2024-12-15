<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface OrbitShipListRetrieverInterface
{
    /**
     * @return array<int, array{ships: array<int, SpacecraftInterface>, name: string}>
     */
    public function retrieve(ColonyInterface $colony): array;
}
