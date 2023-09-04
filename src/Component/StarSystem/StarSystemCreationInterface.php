<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;

interface StarSystemCreationInterface
{
    public function recreateStarSystem(MapInterface $map, string $randomSystemName): ?StarSystemInterface;
}
