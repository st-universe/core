<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;

interface EntityWithLocationInterface
{
    public function getLocation(): Map|StarSystemMap;
}
