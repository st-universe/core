<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

interface SpacecraftCrewCalculatorInterface
{
    public function getMaxCrewCountByRump(SpacecraftRump $shipRump): int;

    public function getCrewObj(SpacecraftRump $shipRump): ?ShipRumpCategoryRoleCrew;

    public function getMaxCrewCountByShip(
        Spacecraft $spacecraft
    ): int;

    /**
     * @param array<Module> $modules
     */
    public function getCrewUsage(array $modules, SpacecraftRump $rump, User $user): int;
}
