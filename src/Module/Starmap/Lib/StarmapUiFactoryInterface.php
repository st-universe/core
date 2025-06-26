<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\User;

interface StarmapUiFactoryInterface
{
    public function createMapSectionHelper(): MapSectionHelper;

    public function createYRow(
        ?Layer $layer,
        int $cury,
        int $minx,
        int $maxx,
        int|StarSystem $system
    ): YRow;

    public function createUserYRow(
        User $user,
        Layer $layer,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ): UserYRow;

    public function createExplorableStarmapItem(
        ExploreableStarMapInterface $exploreableStarMap,
        Layer $layer
    ): ExplorableStarMapItemInterface;
}
