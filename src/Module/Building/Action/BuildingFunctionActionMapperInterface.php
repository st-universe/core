<?php

namespace Stu\Module\Building\Action;

interface BuildingFunctionActionMapperInterface
{
    public function map(int $buildingFunctionId): ?BuildingActionHandlerInterface;
}