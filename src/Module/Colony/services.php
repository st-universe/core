<?php

declare(strict_types=1);

namespace Stu\Module\Colony;

use Stu\Module\Colony\Action\ActivateBuilding\ActivateBuilding;
use Stu\Module\Colony\Action\ActivateBuildings\ActivateBuildings;
use Stu\Module\Colony\Action\ActivateShields\ActivateShields;
use Stu\Module\Colony\Action\AllowImmigration\AllowImmigration;
use Stu\Module\Colony\Action\BeamFrom\BeamFrom;
use Stu\Module\Colony\Action\BeamTo\BeamTo;
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
use Stu\Module\Colony\Action\ManageOrbitalShips\ManageOrbitalShips;
use Stu\Module\Colony\Action\ManageOrbitalShuttles\ManageOrbitalShuttles;
use Stu\Module\Colony\Action\RemoveBuilding\RemoveBuilding;
use Stu\Module\Colony\Action\RemoveWaste\RemoveWaste;
use Stu\Module\Colony\Action\RenameBuildplan\RenameBuildplan;
use Stu\Module\Colony\Action\RenameBuildplan\RenameBuildplanRequest;
use Stu\Module\Colony\Action\RenameBuildplan\RenameBuildplanRequestInterface;
use Stu\Module\Colony\Action\RepairBuilding\RepairBuilding;
use Stu\Module\Colony\Action\RepairShip\RepairShip;
use Stu\Module\Colony\Action\ScrollBuildMenu\ScrollBuildMenu;
use Stu\Module\Colony\Action\SetPopulationLimit\SetPopulationLimit;
use Stu\Module\Colony\Action\StartAirfieldShip\StartAirfieldShip;
use Stu\Module\Colony\Action\SwitchColonyMenu\SwitchColonyMenu;
use Stu\Module\Colony\Action\Terraform\Terraform;
use Stu\Module\Colony\Action\TrainCrew\TrainCrew;
use Stu\Module\Colony\Action\UpgradeBuilding\UpgradeBuilding;
use Stu\Module\Colony\Lib\BuildingAction;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\BuildingMassActionConfiguration;
use Stu\Module\Colony\Lib\BuildingMassActionConfigurationInterface;
use Stu\Module\Colony\Lib\BuildPlanDeleter;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Colony\Lib\ColonyCorrector;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelper;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactory;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoader;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyResetter;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Colony\Lib\CommodityConsumption;
use Stu\Module\Colony\Lib\CommodityConsumptionInterface;
use Stu\Module\Colony\Lib\ModuleQueueLib;
use Stu\Module\Colony\Lib\ModuleQueueLibInterface;
use Stu\Module\Colony\Lib\PlanetColonization;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetriever;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\Overview\Overview;
use Stu\Module\Colony\View\RefreshColonyEps\RefreshColonyEps;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademyRequest;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademyRequestInterface;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfield;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfieldRequest;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfieldRequestInterface;
use Stu\Module\Colony\View\ShowBeamFrom\ShowBeamFrom;
use Stu\Module\Colony\View\ShowBeamFrom\ShowBeamFromRequest;
use Stu\Module\Colony\View\ShowBeamFrom\ShowBeamFromRequestInterface;
use Stu\Module\Colony\View\ShowBeamTo\ShowBeamTo;
use Stu\Module\Colony\View\ShowBeamTo\ShowBeamToRequest;
use Stu\Module\Colony\View\ShowBeamTo\ShowBeamToRequestInterface;
use Stu\Module\Colony\View\ShowBuilding\ShowBuilding;
use Stu\Module\Colony\View\ShowBuilding\ShowBuildingRequest;
use Stu\Module\Colony\View\ShowBuilding\ShowBuildingRequestInterface;
use Stu\Module\Colony\View\ShowBuildingManagement\ShowBuildingManagement;
use Stu\Module\Colony\View\ShowBuildingManagement\ShowBuildingManagementRequest;
use Stu\Module\Colony\View\ShowBuildingManagement\ShowBuildingManagementRequestInterface;
use Stu\Module\Colony\View\ShowBuildMenu\ShowBuildMenu;
use Stu\Module\Colony\View\ShowBuildMenu\ShowBuildMenuRequest;
use Stu\Module\Colony\View\ShowBuildMenu\ShowBuildMenuRequestInterface;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPart;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPartRequest;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPartRequestInterface;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlans;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlansRequest;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlansRequestInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Colony\View\ShowColony\ShowColonyRequest;
use Stu\Module\Colony\View\ShowColony\ShowColonyRequestInterface;
use Stu\Module\Colony\View\ShowEpsBar\ShowEpsBar;
use Stu\Module\Colony\View\ShowEpsBar\ShowEpsBarRequest;
use Stu\Module\Colony\View\ShowEpsBar\ShowEpsBarRequestInterface;
use Stu\Module\Colony\View\ShowField\ShowField;
use Stu\Module\Colony\View\ShowField\ShowFieldRequest;
use Stu\Module\Colony\View\ShowField\ShowFieldRequestInterface;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyardRequest;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyardRequestInterface;
use Stu\Module\Colony\View\ShowGiveUp\ShowGiveUp;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Colony\View\ShowManagement\ShowManagement;
use Stu\Module\Colony\View\ShowManagement\ShowManagementRequest;
use Stu\Module\Colony\View\ShowManagement\ShowManagementRequestInterface;
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
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyardRequest;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyardRequestInterface;
use Stu\Module\Colony\View\ShowShuttleManagement\ShowShuttleManagement;
use Stu\Module\Colony\View\ShowShuttleManagement\ShowShuttleManagementRequest;
use Stu\Module\Colony\View\ShowShuttleManagement\ShowShuttleManagementRequestInterface;
use Stu\Module\Colony\View\ShowSocial\ShowSocial;
use Stu\Module\Colony\View\ShowSocial\ShowSocialRequest;
use Stu\Module\Colony\View\ShowSocial\ShowSocialRequestInterface;
use Stu\Module\Colony\View\ShowStorage\ShowStorage;
use Stu\Module\Colony\View\ShowStorage\ShowStorageRequest;
use Stu\Module\Colony\View\ShowStorage\ShowStorageRequestInterface;
use Stu\Module\Colony\View\ShowSubspaceTelescope\ShowSubspaceTelescope;
use Stu\Module\Colony\View\ShowSubspaceTelescopeScan\ShowSubspaceTelescopeScan;
use Stu\Module\Colony\View\ShowSurface\ShowSurface;
use Stu\Module\Colony\View\ShowSurface\ShowSurfaceRequest;
use Stu\Module\Colony\View\ShowSurface\ShowSurfaceRequestInterface;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFab;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFabRequest;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFabRequestInterface;
use Stu\Module\Colony\View\ShowWaste\ShowWaste;
use Stu\Module\Control\GameController;

use Stu\PlanetGenerator\PlanetGenerator;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

use function DI\autowire;

return [
    BuildingActionInterface::class => autowire(BuildingAction::class),
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
    ShowBeamFromRequestInterface::class => autowire(ShowBeamFromRequest::class),
    ShowBeamToRequestInterface::class => autowire(ShowBeamToRequest::class),
    ShowBuildingRequestInterface::class => autowire(ShowBuildingRequest::class),
    ShowBuildingManagementRequestInterface::class => autowire(ShowBuildingManagementRequest::class),
    ShowBuildMenuRequestInterface::class => autowire(ShowBuildMenuRequest::class),
    ShowBuildMenuPartRequestInterface::class => autowire(ShowBuildMenuPartRequest::class),
    ShowBuildPlansRequestInterface::class => autowire(ShowBuildPlansRequest::class),
    ShowColonyRequestInterface::class => autowire(ShowColonyRequest::class),
    ShowEpsBarRequestInterface::class => autowire(ShowEpsBarRequest::class),
    ShowFieldRequestInterface::class => autowire(ShowFieldRequest::class),
    ShowFighterShipyardRequestInterface::class => autowire(ShowFighterShipyardRequest::class),
    ShowManagementRequestInterface::class => autowire(ShowManagementRequest::class),
    ShowMiscRequestInterface::class => autowire(ShowMiscRequest::class),
    ShowModuleCancelRequestInterface::class => autowire(ShowModuleCancelRequest::class),
    ShowModuleFabRequestInterface::class => autowire(ShowModuleFabRequest::class),
    ShowModuleScreenRequestInterface::class => autowire(ShowModuleScreenRequest::class),
    ShowOrbitManagementRequestInterface::class => autowire(ShowOrbitManagementRequest::class),
    ShowOrbitShiplistRequestInterface::class => autowire(ShowOrbitShiplistRequest::class),
    ShowShipDisassemblyRequestInterface::class => autowire(ShowShipDisassemblyRequest::class),
    ShowShipRepairRequestInterface::class => autowire(ShowShipRepairRequest::class),
    ShowShipyardRequestInterface::class => autowire(ShowShipyardRequest::class),
    ShowShuttleManagementRequestInterface::class => autowire(ShowShuttleManagementRequest::class),
    ShowSocialRequestInterface::class => autowire(ShowSocialRequest::class),
    ShowStorageRequestInterface::class => autowire(ShowStorageRequest::class),
    ShowSurfaceRequestInterface::class => autowire(ShowSurfaceRequest::class),
    ShowTorpedoFabRequestInterface::class => autowire(ShowTorpedoFabRequest::class),
    ColonyCorrectorInterface::class => autowire(ColonyCorrector::class),
    RenameBuildplanRequestInterface::class => autowire(RenameBuildplanRequest::class),
    'COLONY_ACTIONS' => [
        GiveUp::ACTION_IDENTIFIER => autowire(GiveUp::class),
        ActivateBuilding::ACTION_IDENTIFIER => autowire(ActivateBuilding::class),
        ActivateShields::ACTION_IDENTIFIER => autowire(ActivateShields::class),
        AllowImmigration::ACTION_IDENTIFIER => autowire(AllowImmigration::class),
        BeamFrom::ACTION_IDENTIFIER => autowire(BeamFrom::class),
        BeamTo::ACTION_IDENTIFIER => autowire(BeamTo::class),
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
        ManageOrbitalShips::ACTION_IDENTIFIER => autowire(ManageOrbitalShips::class),
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
        RemoveWaste::ACTION_IDENTIFIER => autowire(RemoveWaste::class)
    ],
    'COLONY_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Overview::VIEW_IDENTIFIER => autowire(Overview::class),
        ShowColony::VIEW_IDENTIFIER => autowire(ShowColony::class),
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
        ShowBeamTo::VIEW_IDENTIFIER => autowire(ShowBeamTo::class),
        ShowBeamFrom::VIEW_IDENTIFIER => autowire(ShowBeamFrom::class),
        ShowEpsBar::VIEW_IDENTIFIER => autowire(ShowEpsBar::class),
        ShowStorage::VIEW_IDENTIFIER => autowire(ShowStorage::class),
        ShowOrbitManagement::VIEW_IDENTIFIER => autowire(ShowOrbitManagement::class),
        ShowModuleScreen::VIEW_IDENTIFIER => autowire(ShowModuleScreen::class),
        ShowModuleScreenBuildplan::VIEW_IDENTIFIER => autowire(ShowModuleScreenBuildplan::class),
        ShowModuleFab::VIEW_IDENTIFIER => autowire(ShowModuleFab::class),
        ShowModuleCancel::VIEW_IDENTIFIER => autowire(ShowModuleCancel::class),
        ShowShipRepair::VIEW_IDENTIFIER => autowire(ShowShipRepair::class),
        ShowShipDisassembly::VIEW_IDENTIFIER => autowire(ShowShipDisassembly::class),
        ShowGiveUp::VIEW_IDENTIFIER => autowire(ShowGiveUp::class),
        ShowSectorScan::VIEW_IDENTIFIER => autowire(ShowSectorScan::class),
        ShowPodsLocations::VIEW_IDENTIFIER => autowire(ShowPodsLocations::class),
        ShowShuttleManagement::VIEW_IDENTIFIER => autowire(ShowShuttleManagement::class),
        ShowWaste::VIEW_IDENTIFIER => autowire(ShowWaste::class),
        ShowSubspaceTelescope::VIEW_IDENTIFIER => autowire(ShowSubspaceTelescope::class),
        ShowSubspaceTelescopeScan::VIEW_IDENTIFIER => autowire(ShowSubspaceTelescopeScan::class),
        RefreshColonyEps::VIEW_IDENTIFIER => autowire(RefreshColonyEps::class)
    ],
    BuildPlanDeleterInterface::class => autowire(BuildPlanDeleter::class),
];
