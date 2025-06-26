<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystem;

interface StarSystemCreationInterface
{
    public function recreateStarSystem(Map $map, string $randomSystemName): ?StarSystem;
}
