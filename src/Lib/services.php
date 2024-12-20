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
use Stu\Lib\Pirate\Behaviour\AttackShipBehaviour;
use Stu\Lib\Pirate\Behaviour\CallForSupportBehaviour;
use Stu\Lib\Pirate\Behaviour\ChangeAlertStateToRed;
use Stu\Lib\Pirate\Behaviour\DeactivateShieldsBehaviour;
use Stu\Lib\Pirate\Behaviour\FlyBehaviour;
use Stu\Lib\Pirate\Behaviour\HideBehaviour;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Behaviour\RageBehaviour;
use Stu\Lib\Pirate\Behaviour\RubColonyBehaviour;
use Stu\Lib\Pirate\Behaviour\SearchFriendBehaviour;
use Stu\Lib\Pirate\Component\MoveOnLayer;
use Stu\Lib\Pirate\Component\MoveOnLayerInterface;
use Stu\Lib\Pirate\Component\PirateAttack;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\Component\PirateFlight;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\Component\PirateNavigation;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEps;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\Component\SafeFlightRoute;
use Stu\Lib\Pirate\Component\SafeFlightRouteInterface;
use Stu\Lib\Pirate\Component\TrapDetection;
use Stu\Lib\Pirate\Component\TrapDetectionInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReaction;
use Stu\Lib\Pirate\PirateReactionInterface;
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
use function DI\get;

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
    InteractionMemberFactoryInterface::class => autowire(InteractionMemberFactory::class),
    InteractionCheckerBuilderFactoryInterface::class => autowire(InteractionCheckerBuilderFactory::class),
    StorageManagerInterface::class => autowire(StorageManager::class),
    StorageEntityWrapperFactoryInterface::class => autowire(StorageEntityWrapperFactory::class),
    TransferInformationFactoryInterface::class => autowire(TransferInformationFactory::class),
    TransferStrategyInterface::class => [
        TransferTypeEnum::COMMODITIES->value => autowire(CommodityTransferStrategy::class),
        TransferTypeEnum::CREW->value => autowire(TroopTransferStrategy::class),
        TransferTypeEnum::TORPEDOS->value => autowire(TorpedoTransferStrategy::class)
    ],
    PirateBehaviourInterface::class => [
        PirateBehaviourEnum::FLY->value => autowire(FlyBehaviour::class),
        PirateBehaviourEnum::RUB_COLONY->value => autowire(RubColonyBehaviour::class),
        PirateBehaviourEnum::ATTACK_SHIP->value => autowire(AttackShipBehaviour::class),
        PirateBehaviourEnum::HIDE->value => autowire(HideBehaviour::class),
        PirateBehaviourEnum::RAGE->value => autowire(RageBehaviour::class),
        PirateBehaviourEnum::GO_ALERT_RED->value => autowire(ChangeAlertStateToRed::class),
        PirateBehaviourEnum::CALL_FOR_SUPPORT->value => autowire(CallForSupportBehaviour::class),
        PirateBehaviourEnum::SEARCH_FRIEND->value => autowire(SearchFriendBehaviour::class),
        PirateBehaviourEnum::DEACTIVATE_SHIELDS->value => autowire(DeactivateShieldsBehaviour::class)
    ],
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateReactionInterface::class => autowire(PirateReaction::class)->constructorParameter(
        'behaviours',
        get(PirateBehaviourInterface::class)
    ),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    SafeFlightRouteInterface::class => autowire(SafeFlightRoute::class),
    MoveOnLayerInterface::class => autowire(MoveOnLayer::class),
    PirateNavigationInterface::class => autowire(PirateNavigation::class),
    ReloadMinimalEpsInterface::class => autowire(ReloadMinimalEps::class),
    PirateWrathManagerInterface::class => autowire(PirateWrathManager::class),
    PirateAttackInterface::class => autowire(PirateAttack::class),
    TrapDetectionInterface::class => autowire(TrapDetection::class)
];
