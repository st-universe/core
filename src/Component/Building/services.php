<?php

declare(strict_types=1);

namespace Stu\Module\Building;

use Stu\Component\Building\BuildingManager;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Building\BuildingPostAction;
use Stu\Component\Building\ColonyBuildingEffects;
use Stu\Component\Building\BuildingReactivationHandler;

use function DI\autowire;

return [
    ColonyBuildingEffects::class => autowire(ColonyBuildingEffects::class),
    BuildingReactivationHandler::class => autowire(BuildingReactivationHandler::class),
    BuildingManagerInterface::class => autowire(BuildingManager::class)
        ->constructorParameter(
            'buildingPostAction',
            autowire(BuildingPostAction::class)
        ),
];
