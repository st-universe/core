<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface EntityWithLocationInterface
{
    public function getLocation(): MapInterface|StarSystemMapInterface;
}
