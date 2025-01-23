<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\SpacecraftBuildplanInterface;

interface BuildPlanDeleterInterface
{
    /**
     * Deletes the buildplan
     */
    public function delete(SpacecraftBuildplanInterface $spacecraftBuildplan): void;

    /**
     * Returns `true` if the object is actually deletable
     */
    public function isDeletable(
        SpacecraftBuildplanInterface $spacecraftBuildplan
    ): bool;
}
