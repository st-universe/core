<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\SpacecraftBuildplan;

interface BuildPlanDeleterInterface
{
    /**
     * Deletes the buildplan
     */
    public function delete(SpacecraftBuildplan $spacecraftBuildplan): void;

    /**
     * Returns `true` if the object is actually deletable
     */
    public function isDeletable(
        SpacecraftBuildplan $spacecraftBuildplan
    ): bool;
}
