<?php

declare(strict_types=1);

namespace Stu\Module\Colony;

use Stu\Module\Colony\Action\ActivateBuilding\ActivateBuilding;
use Stu\Module\Colony\Action\ActivateBuildings\ActivateBuildings;
use Stu\Module\Colony\Action\ActivateShields\ActivateShields;
use Stu\Module\Colony\Action\AllowImmigration\AllowImmigration;
use Stu\Module\Colony\Action\BuildAirfieldRump\BuildAirfieldRump;
use Stu\Module\Colony\Action\BuildFighterShipyardRump\BuildFighterShipyardRump;
use Stu\Module\Colony\Action\BuildOnField\BuildOnField;
use Stu\Module\Colony\Action\BuildShip\BuildShip;
use Stu\Module\Colony\Action\BuildTorpedos\BuildTorpedos;
use Stu\Module\Colony\Action\CancelModuleCreation\CancelModuleCreation;
use Stu\Module\Colony\Action\CancelShipRepair\CancelShipRepair;
use Stu\Module\Colony\Action\CancelShipRepair\CancelShipRepairRequest;
use Stu\Module\Colony\Action\CancelShipRepair\CancelShipRepairRequestInterface;
use Stu\Module\Colony\Action\ChangeFrequency\ChangeFrequency;
use Stu\Module\Colony\Action\ChangeName\ChangeName;
use Stu\Module\Colony\Action\ChangeName\ChangeNameRequest;
use Stu\Module\Colony\Action\ChangeName\ChangeNameRequestInterface;
use Stu\Module\Colony\Action\ChangeTorpedoType\ChangeTorpedoType;
use Stu\Module\Colony\Action\ChangeTorpedoType\ChangeTorpedoTypeRequest;
use Stu\Module\Colony\Action\ChangeTorpedoType\ChangeTorpedoTypeRequestInterface;
use Stu\Module\Colony\Action\CreateBuildplan\CreateBuildplan;
use Stu\Module\Colony\Action\CreateModules\CreateModules;
use Stu\Module\Colony\Action\DeactivateBuilding\DeactivateBuilding;
use Stu\Module\Colony\Action\DeactivateBuildings\DeactivateBuildings;
use Stu\Module\Colony\Action\DeactivateShields\DeactivateShields;
use Stu\Module\Colony\Action\DeleteBuildPlan\DeleteBuildPlan;
use Stu\Module\Colony\Action\DenyImmigration\DenyImmigration;
use Stu\Module\Colony\Action\DisassembleShip\DisassembleShip;
use Stu\Module\Colony\Action\GiveUp\GiveUp;
use Stu\Module\Colony\Action\GiveUp\GiveUpRequest;
use Stu\Module\Colony\Action\GiveUp\GiveUpRequestInterface;
use Stu\Module\Colony\Action\LandShip\LandShip;
use Stu\Module\Colony\Action\LoadShields\LoadShields;
use Stu\Module\Colony\Action\ManageOrbitalShuttles\ManageOrbitalShuttles;
use Stu\Module\Colony\Action\ManageOrbitalSpacecrafts\ManageOrbitalSpacecrafts;
use Stu\Module\Colony\Action\RemoveBuilding\RemoveBuilding;
use Stu\Module\Colony\Action\RemoveWaste\RemoveWaste;
use Stu\Module\Colony\Action\RenameBuildplan\RenameBuildplan;
use Stu\Module\Colony\Action\RenameBuildplan\RenameBuildplanRequest;
use Stu\Module\Colony\Action\RenameBuildplan\RenameBuildplanRequestInterface;
use Stu\Module\Colony\Action\RepairBuilding\RepairBuilding;
use Stu\Module\Colony\Action\RepairShip\RepairShip;
use Stu\Module\Colony\Action\RetrofitShip\RetrofitShip;
use Stu\Module\Colony\Action\Sandbox\CreateSandbox;
use Stu\Module\Colony\Action\ScrollBuildMenu\ScrollBuildMenu;
use Stu\Module\Colony\Action\SetPopulationLimit\SetPopulationLimit;
use Stu\Module\Colony\Action\StartAirfieldShip\StartAirfieldShip;
use Stu\Module\Colony\Action\SwitchColonyMenu\SwitchColonyMenu;
use Stu\Module\Colony\Action\Terraform\Terraform;
use Stu\Module\Colony\Action\TrainCrew\TrainCrew;
use Stu\Module\Colony\Action\UpgradeBuilding\UpgradeBuilding;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\BuildingAction;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\BuildingCommodityDeltaTracker;
use Stu\Module\Colony\Lib\BuildingMassActionConfiguration;
use Stu\Module\Colony\Lib\BuildingMassActionConfigurationInterface;
use Stu\Module\Colony\Lib\BuildPlanDeleter;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Colony\Lib\ColonyCorrector;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactory;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoader;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyResetter;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Colony\Lib\CommodityConsumption;
use Stu\Module\Colony\Lib\CommodityConsumptionInterface;
use Stu\Module\Colony\Lib\Damage\ApplyBuildingDamage;
use Stu\Module\Colony\Lib\Damage\ApplyBuildingDamageInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelper;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\Gui\Component\AcademyProvider;
use Stu\Module\Colony\Lib\Gui\Component\AirfieldProvider;
use Stu\Module\Colony\Lib\Gui\Component\BuildingManagementProvider;
use Stu\Module\Colony\Lib\Gui\Component\BuildmenuProvider;
use Stu\Module\Colony\Lib\Gui\Component\EffectsProvider;
use Stu\Module\Colony\Lib\Gui\Component\EpsBarProvider;
use Stu\Module\Colony\Lib\Gui\Component\FighterShipyardProvider;
use Stu\Module\Colony\Lib\Gui\Component\ManagementProvider;
use Stu\Module\Colony\Lib\Gui\Component\ModuleFabProvider;
use Stu\Module\Colony\Lib\Gui\Component\ShieldingProvider;
use Stu\Module\Colony\Lib\Gui\Component\ShipDisassemblyProvider;
use Stu\Module\Colony\Lib\Gui\Component\ShipRepairProvider;
use Stu\Module\Colony\Lib\Gui\Component\ShipRetrofitProvider;
use Stu\Module\Colony\Lib\Gui\Component\ShipyardProvider;
use Stu\Module\Colony\Lib\Gui\Component\SocialProvider;
use Stu\Module\Colony\Lib\Gui\Component\SpacecraftBuildplansProvider;
use Stu\Module\Colony\Lib\Gui\Component\StorageProvider;
use Stu\Module\Colony\Lib\Gui\Component\SurfaceProvider;
use Stu\Module\Colony\Lib\Gui\Component\TorpedoFabProvider;
use Stu\Module\Colony\Lib\ModuleQueueLib;
use Stu\Module\Colony\Lib\ModuleQueueLibInterface;
use Stu\Module\Colony\Lib\PlanetColonization;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetriever;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\RefreshColonyEps\RefreshColonyEps;
use Stu\Module\Colony\View\Sandbox\ShowColonySandbox;
use Stu\Module\Colony\View\Sandbox\ShowNewSandbox;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademyRequest;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademyRequestInterface;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfield;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfieldRequest;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfieldRequestInterface;
use Stu\Module\Colony\View\ShowBuilding\ShowBuilding;
use Stu\Module\Colony\View\ShowBuilding\ShowBuildingRequest;
use Stu\Module\Colony\View\ShowBuilding\ShowBuildingRequestInterface;
use Stu\Module\Colony\View\ShowBuildingManagement\ShowBuildingManagement;
use Stu\Module\Colony\View\ShowBuildMenu\ShowBuildMenu;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPart;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlans;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlansRequest;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlansRequestInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Colony\View\ShowColony\ShowColonyRequest;
use Stu\Module\Colony\View\ShowColony\ShowColonyRequestInterface;
use Stu\Module\Colony\View\ShowField\ShowField;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyardRequest;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyardRequestInterface;
use Stu\Module\Colony\View\ShowGiveUp\ShowGiveUp;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Colony\View\ShowMainscreen\ShowMainscreen;
use Stu\Module\Colony\View\ShowManagement\ShowManagement;
use Stu\Module\Colony\View\ShowMisc\ShowMisc;
use Stu\Module\Colony\View\ShowMisc\ShowMiscRequest;
use Stu\Module\Colony\View\ShowMisc\ShowMiscRequestInterface;
use Stu\Module\Colony\View\ShowModuleCancel\ShowModuleCancel;
use Stu\Module\Colony\View\ShowModuleCancel\ShowModuleCancelRequest;
use Stu\Module\Colony\View\ShowModuleCancel\ShowModuleCancelRequestInterface;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFab;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFabRequest;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFabRequestInterface;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreen;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreenRequest;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreenRequestInterface;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Colony\View\ShowModuleScreenRetrofit\ShowModuleScreenRetrofit;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagementRequest;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagementRequestInterface;
use Stu\Module\Colony\View\ShowOrbitShiplist\ShowOrbitShiplist;
use Stu\Module\Colony\View\ShowOrbitShiplist\ShowOrbitShiplistRequest;
use Stu\Module\Colony\View\ShowOrbitShiplist\ShowOrbitShiplistRequestInterface;
use Stu\Module\Colony\View\ShowPodsLocations\ShowPodsLocations;
use Stu\Module\Colony\View\ShowSectorScan\ShowSectorScan;
use Stu\Module\Colony\View\ShowShipDisassembly\ShowShipDisassembly;
use Stu\Module\Colony\View\ShowShipDisassembly\ShowShipDisassemblyRequest;
use Stu\Module\Colony\View\ShowShipDisassembly\ShowShipDisassemblyRequestInterface;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepair;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepairRequest;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepairRequestInterface;
use Stu\Module\Colony\View\ShowShipRetrofit\ShowShipRetrofit;
use Stu\Module\Colony\View\ShowShipRetrofit\ShowShipRetrofitRequest;
use Stu\Module\Colony\View\ShowShipRetrofit\ShowShipRetrofitRequestInterface;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyardRequest;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyardRequestInterface;
use Stu\Module\Colony\View\ShowShuttleManagement\ShowShuttleManagement;
use Stu\Module\Colony\View\ShowShuttleManagement\ShowShuttleManagementRequest;
use Stu\Module\Colony\View\ShowShuttleManagement\ShowShuttleManagementRequestInterface;
use Stu\Module\Colony\View\ShowSocial\ShowSocial;
use Stu\Module\Colony\View\ShowSubspaceTelescope\ShowSubspaceTelescope;
use Stu\Module\Colony\View\ShowSubspaceTelescopeScan\ShowSubspaceTelescopeScan;
use Stu\Module\Colony\View\ShowSurface\ShowSurface;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFab;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFabRequest;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFabRequestInterface;
use Stu\Module\Colony\View\ShowWaste\ShowWaste;
use Stu\Module\Control\GameController;
use Stu\Module\Game\Action\Transfer\Transfer;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Game\View\ShowTransfer\ShowTransfer;
use Stu\Module\Spacecraft\View\ShowSpacecraftStorage\ShowSpacecraftStorage;
use Stu\PlanetGenerator\PlanetGenerator;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

use function DI\autowire;
use function DI\get;

return [
    ApplyBuildingDamageInterface::class => autowire(ApplyBuildingDamage::class),
    BuildingActionInterface::class => autowire(BuildingAction::class),
    BuildingCommodityDeltaTracker::class => autowire(BuildingCommodityDeltaTracker::class),
    BuildingMassActionConfigurationInterface::class => autowire(BuildingMassActionConfiguration::class),
    CancelShipRepairRequestInterface::class => autowire(CancelShipRepairRequest::class),
    ChangeNameRequestInterface::class => autowire(ChangeNameRequest::class),
    ChangeTorpedoTypeRequestInterface::class => autowire(ChangeTorpedoTypeRequest::class),
    ColonyGuiHelperInterface::class => autowire(ColonyGuiHelper::class),
    ColonyLibFactoryInterface::class => autowire(ColonyLibFactory::class),
    ColonyLoaderInterface::class => autowire(ColonyLoader::class),
    ColonyResetterInterface::class => autowire(ColonyResetter::class),
    CommodityConsumptionInterface::class => autowire(CommodityConsumption::class),
    GiveUpRequestInterface::class => autowire(GiveUpRequest::class),
    ModuleQueueLibInterface::class => autowire(ModuleQueueLib::class),
    PlanetColonizationInterface::class => autowire(PlanetColonization::class),
    PlanetFieldTypeRetrieverInterface::class => autowire(PlanetFieldTypeRetriever::class),
    PlanetGeneratorInterface::class => autowire(PlanetGenerator::class),
    ShowAcademyRequestInterface::class => autowire(ShowAcademyRequest::class),
    ShowAirfieldRequestInterface::class => autowire(ShowAirfieldRequest::class),
    ShowBuildingRequestInterface::class => autowire(ShowBuildingRequest::class),
    ShowBuildPlansRequestInterface::class => autowire(ShowBuildPlansRequest::class),
    ShowColonyRequestInterface::class => autowire(ShowColonyRequest::class),
    ShowFighterShipyardRequestInterface::class => autowire(ShowFighterShipyardRequest::class),
    ShowMiscRequestInterface::class => autowire(ShowMiscRequest::class),
    ShowModuleCancelRequestInterface::class => autowire(ShowModuleCancelRequest::class),
    ShowModuleFabRequestInterface::class => autowire(ShowModuleFabRequest::class),
    ShowModuleScreenRequestInterface::class => autowire(ShowModuleScreenRequest::class),
    ShowOrbitManagementRequestInterface::class => autowire(ShowOrbitManagementRequest::class),
    ShowOrbitShiplistRequestInterface::class => autowire(ShowOrbitShiplistRequest::class),
    ShowShipDisassemblyRequestInterface::class => autowire(ShowShipDisassemblyRequest::class),
    ShowShipRepairRequestInterface::class => autowire(ShowShipRepairRequest::class),
    ShowShipRetrofitRequestInterface::class => autowire(ShowShipRetrofitRequest::class),
    ShowShipyardRequestInterface::class => autowire(ShowShipyardRequest::class),
    ShowShuttleManagementRequestInterface::class => autowire(ShowShuttleManagementRequest::class),
    ShowTorpedoFabRequestInterface::class => autowire(ShowTorpedoFabRequest::class),
    ColonyCorrectorInterface::class => autowire(ColonyCorrector::class),
    RenameBuildplanRequestInterface::class => autowire(RenameBuildplanRequest::class),
    BuildPlanDeleterInterface::class => autowire(BuildPlanDeleter::class),
    'COLONY_ACTIONS' => [
        GiveUp::ACTION_IDENTIFIER => autowire(GiveUp::class),
        ActivateBuilding::ACTION_IDENTIFIER => autowire(ActivateBuilding::class),
        ActivateShields::ACTION_IDENTIFIER => autowire(ActivateShields::class),
        AllowImmigration::ACTION_IDENTIFIER => autowire(AllowImmigration::class),
        BuildAirfieldRump::ACTION_IDENTIFIER => autowire(BuildAirfieldRump::class),
        BuildFighterShipyardRump::ACTION_IDENTIFIER => autowire(BuildFighterShipyardRump::class),
        BuildOnField::ACTION_IDENTIFIER => autowire(BuildOnField::class),
        BuildShip::ACTION_IDENTIFIER => autowire(BuildShip::class),
        CreateBuildplan::ACTION_IDENTIFIER => autowire(CreateBuildplan::class),
        BuildTorpedos::ACTION_IDENTIFIER => autowire(BuildTorpedos::class),
        CancelModuleCreation::ACTION_IDENTIFIER => autowire(CancelModuleCreation::class),
        ChangeName::ACTION_IDENTIFIER => autowire(ChangeName::class),
        CancelShipRepair::ACTION_IDENTIFIER => autowire(CancelShipRepair::class),
        CreateModules::ACTION_IDENTIFIER => autowire(CreateModules::class),
        DeactivateBuilding::ACTION_IDENTIFIER => autowire(DeactivateBuilding::class),
        DeactivateShields::ACTION_IDENTIFIER => autowire(DeactivateShields::class),
        DeleteBuildPlan::ACTION_IDENTIFIER => autowire(DeleteBuildPlan::class),
        DenyImmigration::ACTION_IDENTIFIER => autowire(DenyImmigration::class),
        LandShip::ACTION_IDENTIFIER => autowire(LandShip::class),
        LoadShields::ACTION_IDENTIFIER => autowire(LoadShields::class),
        ManageOrbitalSpacecrafts::ACTION_IDENTIFIER => autowire(ManageOrbitalSpacecrafts::class),
        ManageOrbitalShuttles::ACTION_IDENTIFIER => autowire(ManageOrbitalShuttles::class),
        RemoveBuilding::ACTION_IDENTIFIER => autowire(RemoveBuilding::class),
        RepairBuilding::ACTION_IDENTIFIER => autowire(RepairBuilding::class),
        RepairShip::ACTION_IDENTIFIER => autowire(RepairShip::class),
        ScrollBuildMenu::ACTION_IDENTIFIER => autowire(ScrollBuildMenu::class),
        SetPopulationLimit::ACTION_IDENTIFIER => autowire(SetPopulationLimit::class),
        StartAirfieldShip::ACTION_IDENTIFIER => autowire(StartAirfieldShip::class),
        SwitchColonyMenu::ACTION_IDENTIFIER => autowire(SwitchColonyMenu::class),
        Terraform::ACTION_IDENTIFIER => autowire(Terraform::class),
        TrainCrew::ACTION_IDENTIFIER => autowire(TrainCrew::class),
        UpgradeBuilding::ACTION_IDENTIFIER => autowire(UpgradeBuilding::class),
        ActivateBuildings::ACTION_IDENTIFIER => autowire(ActivateBuildings::class),
        DeactivateBuildings::ACTION_IDENTIFIER => autowire(DeactivateBuildings::class),
        DisassembleShip::ACTION_IDENTIFIER => autowire(DisassembleShip::class),
        ChangeFrequency::ACTION_IDENTIFIER => autowire(ChangeFrequency::class),
        ChangeTorpedoType::ACTION_IDENTIFIER => autowire(ChangeTorpedoType::class),
        RenameBuildplan::ACTION_IDENTIFIER => autowire(RenameBuildplan::class),
        RemoveWaste::ACTION_IDENTIFIER => autowire(RemoveWaste::class),
        RetrofitShip::ACTION_IDENTIFIER => autowire(RetrofitShip::class),
        Transfer::ACTION_IDENTIFIER => get(Transfer::class),
        CreateSandbox::ACTION_IDENTIFIER => get(CreateSandbox::class)
    ],
    'COLONY_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowColony::VIEW_IDENTIFIER => autowire(ShowColony::class),
        ShowMainscreen::VIEW_IDENTIFIER => autowire(ShowMainscreen::class),
        ShowBuildMenu::VIEW_IDENTIFIER => autowire(ShowBuildMenu::class),
        ShowManagement::VIEW_IDENTIFIER => autowire(ShowManagement::class),
        ShowMisc::VIEW_IDENTIFIER => autowire(ShowMisc::class),
        ShowSocial::VIEW_IDENTIFIER => autowire(ShowSocial::class),
        ShowBuildingManagement::VIEW_IDENTIFIER => autowire(ShowBuildingManagement::class),
        ShowShipyard::VIEW_IDENTIFIER => autowire(ShowShipyard::class),
        ShowFighterShipyard::VIEW_IDENTIFIER => autowire(ShowFighterShipyard::class),
        ShowField::VIEW_IDENTIFIER => autowire(ShowField::class),
        ShowAcademy::VIEW_IDENTIFIER => autowire(ShowAcademy::class),
        ShowBuildMenuPart::VIEW_IDENTIFIER => autowire(ShowBuildMenuPart::class),
        ShowTorpedoFab::VIEW_IDENTIFIER => autowire(ShowTorpedoFab::class),
        ShowBuildPlans::VIEW_IDENTIFIER => autowire(ShowBuildPlans::class),
        ShowAirfield::VIEW_IDENTIFIER => autowire(ShowAirfield::class),
        ShowBuilding::VIEW_IDENTIFIER => autowire(ShowBuilding::class),
        ShowInformation::VIEW_IDENTIFIER => autowire(ShowInformation::class),
        ShowSurface::VIEW_IDENTIFIER => autowire(ShowSurface::class),
        ShowOrbitShiplist::VIEW_IDENTIFIER => autowire(ShowOrbitShiplist::class),
        ShowOrbitManagement::VIEW_IDENTIFIER => autowire(ShowOrbitManagement::class),
        ShowModuleScreen::VIEW_IDENTIFIER => autowire(ShowModuleScreen::class),
        ShowModuleScreenBuildplan::VIEW_IDENTIFIER => autowire(ShowModuleScreenBuildplan::class),
        ShowModuleScreenRetrofit::VIEW_IDENTIFIER => autowire(ShowModuleScreenRetrofit::class),
        ShowModuleFab::VIEW_IDENTIFIER => autowire(ShowModuleFab::class),
        ShowModuleCancel::VIEW_IDENTIFIER => autowire(ShowModuleCancel::class),
        ShowShipRepair::VIEW_IDENTIFIER => autowire(ShowShipRepair::class),
        ShowShipRetrofit::VIEW_IDENTIFIER => autowire(ShowShipRetrofit::class),
        ShowShipDisassembly::VIEW_IDENTIFIER => autowire(ShowShipDisassembly::class),
        ShowGiveUp::VIEW_IDENTIFIER => autowire(ShowGiveUp::class),
        ShowSectorScan::VIEW_IDENTIFIER => autowire(ShowSectorScan::class),
        ShowSpacecraftStorage::VIEW_IDENTIFIER => autowire(ShowSpacecraftStorage::class),
        ShowPodsLocations::VIEW_IDENTIFIER => autowire(ShowPodsLocations::class),
        ShowShuttleManagement::VIEW_IDENTIFIER => autowire(ShowShuttleManagement::class),
        ShowWaste::VIEW_IDENTIFIER => autowire(ShowWaste::class),
        ShowSubspaceTelescope::VIEW_IDENTIFIER => autowire(ShowSubspaceTelescope::class),
        ShowSubspaceTelescopeScan::VIEW_IDENTIFIER => autowire(ShowSubspaceTelescopeScan::class),
        ShowTransfer::VIEW_IDENTIFIER => get(ShowTransfer::class),
        RefreshColonyEps::VIEW_IDENTIFIER => autowire(RefreshColonyEps::class),
        ShowColonySandbox::VIEW_IDENTIFIER => autowire(ShowColonySandbox::class),
        ShowNewSandbox::VIEW_IDENTIFIER => autowire(ShowNewSandbox::class)
    ],
    'COLONY_COMPONENTS' =>
    [
        ColonyComponentEnum::SURFACE->value => autowire(SurfaceProvider::class),
        ColonyComponentEnum::EFFECTS->value => autowire(EffectsProvider::class),
        ColonyComponentEnum::STORAGE->value => autowire(StorageProvider::class),
        ColonyComponentEnum::SHIELDING->value => autowire(ShieldingProvider::class),
        ColonyComponentEnum::BUILDING_MANAGEMENT->value => autowire(BuildingManagementProvider::class),
        ColonyComponentEnum::EPS_BAR->value => autowire(EpsBarProvider::class),
        ColonyComponentEnum::ACADEMY->value => autowire(AcademyProvider::class),
        ColonyComponentEnum::BUILD_MENUES->value => autowire(BuildmenuProvider::class),
        ColonyComponentEnum::MANAGEMENT->value => autowire(ManagementProvider::class),
        ColonyComponentEnum::SOCIAL->value => autowire(SocialProvider::class),
        ColonyComponentEnum::AIRFIELD->value => autowire(AirfieldProvider::class),
        ColonyComponentEnum::MODULE_FAB->value => autowire(ModuleFabProvider::class),
        ColonyComponentEnum::TORPEDO_FAB->value => autowire(TorpedoFabProvider::class),
        ColonyComponentEnum::SHIPYARD->value => autowire(ShipyardProvider::class),
        ColonyComponentEnum::FIGHTER_SHIPYARD->value => autowire(FighterShipyardProvider::class),
        ColonyComponentEnum::SHIP_BUILDPLANS->value => autowire(SpacecraftBuildplansProvider::class),
        ColonyComponentEnum::SHIP_REPAIR->value => autowire(ShipRepairProvider::class),
        ColonyComponentEnum::SHIP_RETROFIT->value => autowire(ShipRetrofitProvider::class),
        ColonyComponentEnum::SHIP_DISASSEMBLY->value => autowire(ShipDisassemblyProvider::class)
    ]
];
