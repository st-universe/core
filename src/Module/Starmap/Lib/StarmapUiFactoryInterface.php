<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

interface StarmapUiFactoryInterface
{
    public function createMapSectionHelper(): MapSectionHelper;

    public function createYRow(
        int $layerId,
        int $cury,
        int $minx,
        int $maxx,
        int|StarSystemInterface $system
    ): YRow;

    public function createUserYRow(
        UserInterface $user,
        int $layerId,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ): UserYRow;

    public function createExplorableStarmapItem(
        ExploreableStarMapInterface $exploreableStarMap
    ): ExplorableStarMapItem;
}
