<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Stu\Orm\Entity\MapInterface;

interface StarSystemCreationInterface
{
    public function recreateStarSystem(MapInterface $map): void;
}
