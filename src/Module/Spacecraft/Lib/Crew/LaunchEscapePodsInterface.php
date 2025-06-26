<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Orm\Entity\Spacecraft;

interface LaunchEscapePodsInterface
{
    public function launch(Spacecraft $spacecraft): ?Spacecraft;
}
