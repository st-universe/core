<?php

declare(strict_types=1);

namespace Stu\Module\Building;

use Stu\Component\Building\BuildingManager;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Building\BuildingPostAction;
use Stu\Component\Building\ColonyBuildingEffects;
use Stu\Component\Building\BuildingReactivationHandler;
use Stu\Component\Building\BuildingActivationHandler;
use Stu\Component\Building\BuildingFinishHandler;
use Stu\Component\Building\BuildingRemovalHandler;

use function DI\autowire;

return [
    ColonyBuildingEffects::class => autowire(ColonyBuildingEffects::class),
    BuildingReactivationHandler::class => autowire(BuildingReactivationHandler::class),
    BuildingActivationHandler::class => autowire(BuildingActivationHandler::class)
        ->constructorParameter(
            'buildingPostAction',
            autowire(BuildingPostAction::class)
        ),
    BuildingFinishHandler::class => autowire(BuildingFinishHandler::class),
    BuildingRemovalHandler::class => autowire(BuildingRemovalHandler::class),
    BuildingManagerInterface::class => autowire(BuildingManager::class),
];
