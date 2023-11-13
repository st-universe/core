<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Lib\BeamUtil\BeamUtil;
use Stu\Lib\BeamUtil\BeamUtilInterface;
use Stu\Lib\Map\DistanceCalculation;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactory;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactoryInterface;
use Stu\Lib\ModuleScreen\GradientColor;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\ShipManagement\HandleManagers;
use Stu\Lib\ShipManagement\HandleManagersInterface;
use Stu\Lib\ShipManagement\Manager\ManageBattery;
use Stu\Lib\ShipManagement\Manager\ManageMan;
use Stu\Lib\ShipManagement\Manager\ManageReactor;
use Stu\Lib\ShipManagement\Manager\ManageTorpedo;
use Stu\Lib\ShipManagement\Manager\ManageUnman;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactory;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactoryInterface;

use function DI\autowire;
use function DI\create;

return [
    UuidGeneratorInterface::class => autowire(UuidGenerator::class),
    ManagerProviderFactoryInterface::class => autowire(ManagerProviderFactory::class),
    ModuleSelectorAddonFactoryInterface::class => autowire(ModuleSelectorAddonFactory::class),
    GradientColorInterface::class => autowire(GradientColor::class),
    DistanceCalculationInterface::class => autowire(DistanceCalculation::class),
    BeamUtilInterface::class => autowire(BeamUtil::class),
    HandleManagersInterface::class => create(HandleManagers::class)->constructor(
        [
            autowire(ManageBattery::class),
            autowire(ManageMan::class),
            autowire(ManageUnman::class),
            autowire(ManageReactor::class),
            autowire(ManageTorpedo::class),
        ]
    )
];
