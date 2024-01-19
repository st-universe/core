<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Lib\Colony\PlanetFieldHostProvider;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Map\DistanceCalculation;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\BorderDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\ColonyShieldDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\MapDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountDataProviderFactory;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactory;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreation;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactory;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactoryInterface;
use Stu\Lib\ModuleScreen\GradientColor;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\ShipManagement\HandleManagers;
use Stu\Lib\ShipManagement\HandleManagersInterface;
use Stu\Lib\ShipManagement\Manager\ManageBattery;
use Stu\Lib\ShipManagement\Manager\ManageCrew;
use Stu\Lib\ShipManagement\Manager\ManageReactor;
use Stu\Lib\ShipManagement\Manager\ManageTorpedo;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactory;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\Transfer\BeamUtil;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Lib\Transfer\Strategy\CommodityTransferStrategy;
use Stu\Lib\Transfer\Strategy\TorpedoTransferStrategy;
use Stu\Lib\Transfer\Strategy\TroopTransferStrategy;
use Stu\Lib\Transfer\TransferTargetLoader;
use Stu\Lib\Transfer\TransferTargetLoaderInterface;
use Stu\Lib\Transfer\TransferTypeEnum;

use function DI\autowire;
use function DI\create;

return [
    UuidGeneratorInterface::class => autowire(UuidGenerator::class),
    ManagerProviderFactoryInterface::class => autowire(ManagerProviderFactory::class),
    ModuleSelectorAddonFactoryInterface::class => autowire(ModuleSelectorAddonFactory::class),
    GradientColorInterface::class => autowire(GradientColor::class),
    DistanceCalculationInterface::class => autowire(DistanceCalculation::class),
    BeamUtilInterface::class => autowire(BeamUtil::class),
    PlanetFieldHostProviderInterface::class => autowire(PlanetFieldHostProvider::class),
    TransferTargetLoaderInterface::class => autowire(TransferTargetLoader::class),
    HandleManagersInterface::class => create(HandleManagers::class)->constructor(
        [
            autowire(ManageBattery::class),
            autowire(ManageCrew::class),
            autowire(ManageReactor::class),
            autowire(ManageTorpedo::class),
        ]
    ),
    SubspaceDataProviderFactoryInterface::class => autowire(SubspaceDataProviderFactory::class),
    ShipcountDataProviderFactoryInterface::class => autowire(ShipcountDataProviderFactory::class),
    PanelLayerCreationInterface::class => autowire(PanelLayerCreation::class)->constructorParameter(
        'dataProviders',
        [
            PanelLayerEnum::SYSTEM->value => autowire(MapDataProvider::class),
            PanelLayerEnum::MAP->value => autowire(MapDataProvider::class),
            PanelLayerEnum::COLONY_SHIELD->value => autowire(ColonyShieldDataProvider::class),
            PanelLayerEnum::BORDER->value => autowire(BorderDataProvider::class)
        ]
    ),
    'transferStrategies' => [
        TransferTypeEnum::COMMODITIES->value => autowire(CommodityTransferStrategy::class),
        TransferTypeEnum::CREW->value => autowire(TroopTransferStrategy::class),
        TransferTypeEnum::TORPEDOS->value => autowire(TorpedoTransferStrategy::class)
    ]
];
