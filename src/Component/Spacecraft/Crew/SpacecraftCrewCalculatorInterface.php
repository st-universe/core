<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface SpacecraftCrewCalculatorInterface
{
    public function getMaxCrewCountByRump(SpacecraftRumpInterface $shipRump): int;

    public function getCrewObj(SpacecraftRumpInterface $shipRump): ?ShipRumpCategoryRoleCrewInterface;

    public function getMaxCrewCountByShip(
        SpacecraftInterface $spacecraft
    ): int;

    /**
     * @param array<ModuleInterface> $modules
     */
    public function getCrewUsage(array $modules, SpacecraftRumpInterface $rump, UserInterface $user): int;
}
