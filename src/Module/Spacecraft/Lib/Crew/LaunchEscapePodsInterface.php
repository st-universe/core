<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Orm\Entity\SpacecraftInterface;

interface LaunchEscapePodsInterface
{
    public function launch(SpacecraftInterface $spacecraft): ?SpacecraftInterface;
}
