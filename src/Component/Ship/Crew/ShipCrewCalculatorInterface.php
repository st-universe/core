<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Crew;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;

interface ShipCrewCalculatorInterface
{
    public function getMaxCrewCountByRump(ShipRumpInterface $shipRump): int;

    public function getCrewObj(ShipRumpInterface $shipRump): ?ShipRumpCategoryRoleCrewInterface;

    public function getMaxCrewCountByShip(
        ShipInterface $ship
    ): int;

    /**
     * @param array<ModuleInterface> $modules
     */
    public function getCrewUsage(array $modules, ShipRumpInterface $rump, UserInterface $user): int;
}
