<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface ColonyCreationInterface
{
    public function create(StarSystemMapInterface $systemMap, string $name): ColonyInterface;
}
