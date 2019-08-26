<?php

declare(strict_types=1);

namespace Stu\Module\Colony;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Colony\Action\Abandon\Abandon;
use Stu\Module\Colony\Action\Abandon\AbandonRequest;
use Stu\Module\Colony\Action\Abandon\AbandonRequestInterface;
use Stu\Module\Colony\Action\ActivateBuilding\ActivateBuilding;
use Stu\Module\Colony\Action\ActivateBuildings\ActivateBuildings;
use Stu\Module\Colony\Action\ActivateBuildingsEps\ActivateBuildingsEps;
use Stu\Module\Colony\Action\ActivateBuildingsEpsProducer\ActivateBuildingsEpsProducer;
use Stu\Module\Colony\Action\ActivateBuildingsGood\ActivateBuildingsGood;
use Stu\Module\Colony\Action\ActivateBuildingsGoodProducer\ActivateBuildingsGoodProducer;
use Stu\Module\Colony\Action\ActivateBuildingsIndustry\ActivateBuildingsIndustry;
use Stu\Module\Colony\Action\ActivateBuildingsResidentials\ActivateBuildingsResidentials;
use Stu\Module\Colony\Action\AllowImmigration\AllowImmigration;
use Stu\Module\Colony\Action\BeamFrom\BeamFrom;
use Stu\Module\Colony\Action\BeamTo\BeamTo;
use Stu\Module\Colony\Action\BuildAirfieldRump\BuildAirfieldRump;
use Stu\Module\Colony\Action\BuildFighterShipyardRump\BuildFighterShipyardRump;
use Stu\Module\Colony\Action\BuildOnField\BuildOnField;
use Stu\Module\Colony\Action\BuildShip\BuildShip;
use Stu\Module\Colony\Action\BuildTorpedos\BuildTorpedos;
use Stu\Module\Colony\Action\CancelModuleCreation\CancelModuleCreation;
use Stu\Module\Colony\Action\ChangeName\ChangeName;
use Stu\Module\Colony\Action\CreateModules\CreateModules;
use Stu\Module\Colony\Action\DeactivateBuilding\DeactivateBuilding;
use Stu\Module\Colony\Action\DeactivateBuildings\DeactivateBuildings;
use Stu\Module\Colony\Action\DeactivateBuildingsEps\DeactivateBuildingsEps;
use Stu\Module\Colony\Action\DeactivateBuildingsEpsProducer\DeactivateBuildingsEpsProducer;
use Stu\Module\Colony\Action\DeactivateBuildingsGood\DeactivateBuildingsGood;
use Stu\Module\Colony\Action\DeactivateBuildingsGoodProducer\DeactivateBuildingsGoodProducer;
use Stu\Module\Colony\Action\DeactivateBuildingsIndustry\DeactivateBuildingsIndustry;
use Stu\Module\Colony\Action\DeactivateBuildingsResidentials\DeactivateBuildingsResidentials;
use Stu\Module\Colony\Action\DeleteBuildPlan\DeleteBuildPlan;
use Stu\Module\Colony\Action\DenyImmigration\DenyImmigration;
use Stu\Module\Colony\Action\LandShip\LandShip;
use Stu\Module\Colony\Action\ManageOrbitalShips\ManageOrbitalShips;
use Stu\Module\Colony\Action\RemoveBuilding\RemoveBuilding;
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
use Stu\Module\Colony\Lib\ColonyGuiHelper;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoader;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\Overview\Overview;
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
use Stu\Module\Colony\View\ShowBuildResult\ShowBuildResult;
use Stu\Module\Colony\View\ShowBuildResult\ShowBuildResultRequest;
use Stu\Module\Colony\View\ShowBuildResult\ShowBuildResultRequestInterface;
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
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplanRequest;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplanRequestInterface;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagementRequest;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagementRequestInterface;
use Stu\Module\Colony\View\ShowOrbitShiplist\ShowOrbitShiplist;
use Stu\Module\Colony\View\ShowOrbitShiplist\ShowOrbitShiplistRequest;
use Stu\Module\Colony\View\ShowOrbitShiplist\ShowOrbitShiplistRequestInterface;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepair;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepairRequest;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepairRequestInterface;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyardRequest;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyardRequestInterface;
use Stu\Module\Colony\View\ShowSocial\ShowSocial;
use Stu\Module\Colony\View\ShowSocial\ShowSocialRequest;
use Stu\Module\Colony\View\ShowSocial\ShowSocialRequestInterface;
use Stu\Module\Colony\View\ShowStorage\ShowStorage;
use Stu\Module\Colony\View\ShowStorage\ShowStorageRequest;
use Stu\Module\Colony\View\ShowStorage\ShowStorageRequestInterface;
use Stu\Module\Colony\View\ShowSurface\ShowSurface;
use Stu\Module\Colony\View\ShowSurface\ShowSurfaceRequest;
use Stu\Module\Colony\View\ShowSurface\ShowSurfaceRequestInterface;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFab;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFabRequest;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFabRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    BuildingActionInterface::class => autowire(BuildingAction::class),
    ColonyGuiHelperInterface::class => autowire(ColonyGuiHelper::class),
    ColonyLoaderInterface::class => autowire(ColonyLoader::class),
    AbandonRequestInterface::class => autowire(AbandonRequest::class),
    ShowAcademyRequestInterface::class => autowire(ShowAcademyRequest::class),
    ShowAirfieldRequestInterface::class => autowire(ShowAirfieldRequest::class),
    ShowBeamFromRequestInterface::class => autowire(ShowBeamFromRequest::class),
    ShowBeamToRequestInterface::class => autowire(ShowBeamToRequest::class),
    ShowBuildingRequestInterface::class => autowire(ShowBuildingRequest::class),
    ShowBuildingManagementRequestInterface::class => autowire(ShowBuildingManagementRequest::class),
    ShowBuildMenuRequestInterface::class => autowire(ShowBuildMenuRequest::class),
    ShowBuildMenuPartRequestInterface::class => autowire(ShowBuildMenuPartRequest::class),
    ShowBuildPlansRequestInterface::class => autowire(ShowBuildPlansRequest::class),
    ShowBuildResultRequestInterface::class => autowire(ShowBuildResultRequest::class),
    ShowColonyRequestInterface::class => autowire(ShowColonyRequest::class),
    ShowEpsBarRequestInterface::class => autowire(ShowEpsBarRequest::class),
    ShowFieldRequestInterface::class => autowire(ShowFieldRequest::class),
    ShowFighterShipyardRequestInterface::class => autowire(ShowFighterShipyardRequest::class),
    ShowManagementRequestInterface::class => autowire(ShowManagementRequest::class),
    ShowMiscRequestInterface::class => autowire(ShowMiscRequest::class),
    ShowModuleCancelRequestInterface::class => autowire(ShowModuleCancelRequest::class),
    ShowModuleFabRequestInterface::class => autowire(ShowModuleFabRequest::class),
    ShowModuleScreenRequestInterface::class => autowire(ShowModuleScreenRequest::class),
    ShowModuleScreenBuildplanRequestInterface::class => autowire(ShowModuleScreenBuildplanRequest::class),
    ShowOrbitManagementRequestInterface::class => autowire(ShowOrbitManagementRequest::class),
    ShowOrbitShiplistRequestInterface::class => autowire(ShowOrbitShiplistRequest::class),
    ShowShipRepairRequestInterface::class => autowire(ShowShipRepairRequest::class),
    ShowShipyardRequestInterface::class => autowire(ShowShipyardRequest::class),
    ShowSocialRequestInterface::class => autowire(ShowSocialRequest::class),
    ShowStorageRequestInterface::class => autowire(ShowStorageRequest::class),
    ShowSurfaceRequestInterface::class => autowire(ShowSurfaceRequest::class),
    ShowTorpedoFabRequestInterface::class => autowire(ShowTorpedoFabRequest::class),
    IntermediateController::TYPE_COLONY_LIST => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                Abandon::ACTION_IDENTIFIER => autowire(Abandon::class),
                ActivateBuilding::ACTION_IDENTIFIER => autowire(ActivateBuilding::class),
                AllowImmigration::ACTION_IDENTIFIER => autowire(AllowImmigration::class),
                BeamFrom::ACTION_IDENTIFIER => autowire(BeamFrom::class),
                BeamTo::ACTION_IDENTIFIER => autowire(BeamTo::class),
                BuildAirfieldRump::ACTION_IDENTIFIER => autowire(BuildAirfieldRump::class),
                BuildFighterShipyardRump::ACTION_IDENTIFIER => autowire(BuildFighterShipyardRump::class),
                BuildOnField::ACTION_IDENTIFIER => autowire(BuildOnField::class),
                BuildShip::ACTION_IDENTIFIER => autowire(BuildShip::class),
                BuildTorpedos::ACTION_IDENTIFIER => autowire(BuildTorpedos::class),
                CancelModuleCreation::ACTION_IDENTIFIER => autowire(CancelModuleCreation::class),
                ChangeName::ACTION_IDENTIFIER => autowire(ChangeName::class),
                CreateModules::ACTION_IDENTIFIER => autowire(CreateModules::class),
                DeactivateBuilding::ACTION_IDENTIFIER => autowire(DeactivateBuilding::class),
                DeleteBuildPlan::ACTION_IDENTIFIER => autowire(DeleteBuildPlan::class),
                DenyImmigration::ACTION_IDENTIFIER => autowire(DenyImmigration::class),
                LandShip::ACTION_IDENTIFIER => autowire(LandShip::class),
                ManageOrbitalShips::ACTION_IDENTIFIER => autowire(ManageOrbitalShips::class),
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
                ActivateBuildingsEps::ACTION_IDENTIFIER => autowire(ActivateBuildingsEps::class),
                DeactivateBuildingsEps::ACTION_IDENTIFIER => autowire(DeactivateBuildingsEps::class),
                ActivateBuildingsEpsProducer::ACTION_IDENTIFIER => autowire(ActivateBuildingsEpsProducer::class),
                DeactivateBuildingsEpsProducer::ACTION_IDENTIFIER => autowire(DeactivateBuildingsEpsProducer::class),
                ActivateBuildingsGood::ACTION_IDENTIFIER => autowire(ActivateBuildingsGood::class),
                DeactivateBuildingsGood::ACTION_IDENTIFIER => autowire(DeactivateBuildingsGood::class),
                ActivateBuildingsGoodProducer::ACTION_IDENTIFIER => autowire(ActivateBuildingsGoodProducer::class),
                DeactivateBuildingsGoodProducer::ACTION_IDENTIFIER => autowire(DeactivateBuildingsGoodProducer::class),
                ActivateBuildingsResidentials::ACTION_IDENTIFIER => autowire(ActivateBuildingsResidentials::class),
                DeactivateBuildingsResidentials::ACTION_IDENTIFIER => autowire(DeactivateBuildingsResidentials::class),
                ActivateBuildingsIndustry::ACTION_IDENTIFIER => autowire(ActivateBuildingsIndustry::class),
                DeactivateBuildingsIndustry::ACTION_IDENTIFIER => autowire(DeactivateBuildingsIndustry::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
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
                ShowBuildResult::VIEW_IDENTIFIER => autowire(ShowBuildResult::class),
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
            ]
        ),
];