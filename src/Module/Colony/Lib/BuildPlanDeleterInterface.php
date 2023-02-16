<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ShipBuildplanInterface;

interface BuildPlanDeleterInterface
{
    /**
     * Deletes the buildplan
     */
    public function delete(ShipBuildplanInterface $shipBuildplan): void;

    /**
     * Returns `true` if the object is actually deletable
     */
    public function isDeletable(
        ShipBuildplanInterface $shipBuildplan
    ): bool;
}
