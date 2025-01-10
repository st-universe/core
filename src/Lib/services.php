<?php

declare(strict_types=1);

namespace Stu\Lib;

use Psr\Container\ContainerInterface;
use Stu\Lib\Colony\PlanetFieldHostProvider;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentLoader;
use Stu\Lib\Component\ComponentLoaderInterface;
use Stu\Lib\Component\ComponentRegistration;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Information\InformationFactory;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactory;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\Member\InteractionMemberFactory;
use Stu\Lib\Interaction\Member\InteractionMemberFactoryInterface;
use Stu\Lib\Mail\MailFactory;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Map\DistanceCalculation;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\BorderDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\ColonyShieldDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\MapDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountDataProviderFactory;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactory;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreation;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactory;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactoryInterface;
use Stu\Lib\ModuleScreen\GradientColor;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\SpacecraftManagement\HandleManagers;
use Stu\Lib\SpacecraftManagement\HandleManagersInterface;
use Stu\Lib\SpacecraftManagement\Manager\ManageBattery;
use Stu\Lib\SpacecraftManagement\Manager\ManageCrew;
use Stu\Lib\SpacecraftManagement\Manager\ManageReactor;
use Stu\Lib\SpacecraftManagement\Manager\ManageTorpedo;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderFactory;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\Storage\StorageManager;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Transfer\Strategy\CommodityTransferStrategy;
use Stu\Lib\Transfer\Strategy\TorpedoTransferStrategy;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\Strategy\TroopTransferStrategy;
use Stu\Lib\Transfer\TransferInformationFactory;
use Stu\Lib\Transfer\TransferInformationFactoryInterface;
use Stu\Lib\Transfer\TransferEntityLoader;
use Stu\Lib\Transfer\TransferEntityLoaderInterface;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperFactory;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperFactoryInterface;
use Stu\Module\Config\StuConfigInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

use function DI\autowire;
use function DI\create;

return [
    ComponentRegistrationInterface::class => autowire(ComponentRegistration::class),
    ComponentLoaderInterface::class => autowire(ComponentLoader::class),
    SessionStringFactoryInterface::class => autowire(SessionStringFactory::class),
    InformationFactoryInterface::class => autowire(InformationFactory::class),
    UuidGeneratorInterface::class => autowire(UuidGenerator::class),
    ManagerProviderFactoryInterface::class => autowire(ManagerProviderFactory::class),
    ModuleSelectorAddonFactoryInterface::class => autowire(ModuleSelectorAddonFactory::class),
    GradientColorInterface::class => autowire(GradientColor::class),
    DistanceCalculationInterface::class => autowire(DistanceCalculation::class),
    CommodityTransferInterface::class => autowire(CommodityTransfer::class),
    MailerInterface::class => function (ContainerInterface $c): MailerInterface {
        $stuConfig = $c->get(StuConfigInterface::class);
        $transportDsn = $stuConfig->getGameSettings()->getEmailSettings()->getTransportDsn();

        return new Mailer(Transport::fromDsn($transportDsn));
    },
    MailFactoryInterface::class => autowire(MailFactory::class),
    PlanetFieldHostProviderInterface::class => autowire(PlanetFieldHostProvider::class),
    TransferEntityLoaderInterface::class => autowire(TransferEntityLoader::class),
    HandleManagersInterface::class => create(HandleManagers::class)->constructor(
        [
            autowire(ManageBattery::class),
            autowire(ManageCrew::class),
            autowire(ManageReactor::class),
            autowire(ManageTorpedo::class),
        ]
    ),
    SubspaceDataProviderFactoryInterface::class => autowire(SubspaceDataProviderFactory::class),
    SpacecraftCountDataProviderFactoryInterface::class => autowire(SpacecraftCountDataProviderFactory::class),
    PanelLayerCreationInterface::class => autowire(PanelLayerCreation::class)->constructorParameter(
        'dataProviders',
        [
            PanelLayerEnum::SYSTEM->value => autowire(MapDataProvider::class),
            PanelLayerEnum::MAP->value => autowire(MapDataProvider::class),
            PanelLayerEnum::COLONY_SHIELD->value => autowire(ColonyShieldDataProvider::class),
            PanelLayerEnum::BORDER->value => autowire(BorderDataProvider::class)
        ]
    ),
    InteractionMemberFactoryInterface::class => autowire(InteractionMemberFactory::class),
    InteractionCheckerBuilderFactoryInterface::class => autowire(InteractionCheckerBuilderFactory::class),
    StorageManagerInterface::class => autowire(StorageManager::class),
    StorageEntityWrapperFactoryInterface::class => autowire(StorageEntityWrapperFactory::class),
    TransferInformationFactoryInterface::class => autowire(TransferInformationFactory::class),
    TransferStrategyInterface::class => [
        TransferTypeEnum::COMMODITIES->value => autowire(CommodityTransferStrategy::class),
        TransferTypeEnum::CREW->value => autowire(TroopTransferStrategy::class),
        TransferTypeEnum::TORPEDOS->value => autowire(TorpedoTransferStrategy::class)
    ]
];
