<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;

interface BuildingPostActionInterface
{
    public function handleDeactivation(
        Building $building,
        Colony|ColonySandbox $host,
        PlanetField $field
    ): void;

    public function handleActivation(
        Building $building,
        Colony|ColonySandbox $host,
        PlanetField $field
    ): void;
}
