<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;

interface BuildingPostActionInterface
{
    public function handleDeactivation(
        Building $building,
        Colony|ColonySandbox $host
    ): void;

    public function handleActivation(
        Building $building,
        Colony|ColonySandbox $host
    ): void;
}
