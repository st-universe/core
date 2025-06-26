<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\StarSystemMap;

interface ColonyCreationInterface
{
    public function create(StarSystemMap $systemMap, string $name): Colony;
}
