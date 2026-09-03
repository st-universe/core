<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;

interface BuildingFunctionActionMapperInterface
{
    public function map(BuildingFunctionEnum $buildingFunction): ?BuildingActionHandlerInterface;
}
