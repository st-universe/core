<?php

declare(strict_types=1);

namespace Stu\Module\Ship;

use Stu\Module\Ship\Action\ChangeName\ChangeNameRequest;
use Stu\Module\Ship\Action\ChangeName\ChangeNameRequestInterface;
use Stu\Module\Ship\Action\RenameCrew\RenameCrewRequest;
use Stu\Module\Ship\Action\RenameCrew\RenameCrewRequestInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamage;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhase;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\FightLib;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhase;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelper;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\AlertRedHelper;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\AstroEntryLib;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\PositionChecker;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Ship\Lib\ShipAttackCycle;
use Stu\Module\Ship\Lib\ShipAttackCycleInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Ship\Action\ActivateAstroLaboratory\ActivateAstroLaboratory;
use Stu\Module\Ship\Action\ActivateCloak\ActivateCloak;
use Stu\Module\Ship\Action\ActivateSubspace\ActivateSubspace;
use Stu\Module\Ship\Action\ActivateTachyon\ActivateTachyon;
use Stu\Module\Ship\Action\ActivateLss\ActivateLss;
use Stu\Module\Ship\Action\ActivateNbs\ActivateNbs;
use Stu\Module\Ship\Action\ActivatePhaser\ActivatePhaser;
use Stu\Module\Ship\Action\ActivateShields\ActivateShields;
use Stu\Module\Ship\Action\ActivateTorpedo\ActivateTorpedo;
use Stu\Module\Ship\Action\ActivateTractorBeam\ActivateTractorBeam;
use Stu\Module\Ship\Action\ActivateUplink\ActivateUplink;
use Stu\Module\Ship\Action\ActivateWarp\ActivateWarp;
use Stu\Module\Ship\Action\AstroMapping\PlanAstroMapping;
use Stu\Module\Ship\Action\AstroMapping\StartAstroMapping;
use Stu\Module\Ship\Action\AttackShip\AttackShip;
use Stu\Module\Ship\Action\AttackBuilding\AttackBuilding;
use Stu\Module\Ship\Action\BeamFrom\BeamFrom;
use Stu\Module\Ship\Action\BeamFromColony\BeamFromColony;
use Stu\Module\Ship\Action\BeamTo\BeamTo;
use Stu\Module\Ship\Action\BeamToColony\BeamToColony;
use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Module\Ship\Action\BuyTradeLicense\BuyTradeLicense;
use Stu\Module\Ship\Action\ChangeFleetFixation\ChangeFleetFixation;
use Stu\Module\Ship\Action\ChangeFleetFleader\ChangeFleetFleader;
use Stu\Module\Ship\Action\ChangeName\ChangeName;
use Stu\Module\Ship\Action\Colonize\Colonize;
use Stu\Module\Ship\Action\ColonyBlocking\StartBlocking;
use Stu\Module\Ship\Action\ColonyBlocking\StopBlocking;
use Stu\Module\Ship\Action\ColonyDefending\StartDefending;
use Stu\Module\Ship\Action\ColonyDefending\StopDefending;
use Stu\Module\Ship\Action\CreateFleet\CreateFleet;
use Stu\Module\Ship\Action\CreateFleet\CreateFleetRequest;
use Stu\Module\Ship\Action\CreateFleet\CreateFleetRequestInterface;
use Stu\Module\Ship\Action\DeactivateAstroLaboratory\DeactivateAstroLaboratory;
use Stu\Module\Ship\Action\DeactivateCloak\DeactivateCloak;
use Stu\Module\Ship\Action\DeactivateSubspace\DeactivateSubspace;
use Stu\Module\Ship\Action\DeactivateTachyon\DeactivateTachyon;
use Stu\Module\Ship\Action\DeactivateLss\DeactivateLss;
use Stu\Module\Ship\Action\DeactivateNbs\DeactivateNbs;
use Stu\Module\Ship\Action\DeactivatePhaser\DeactivatePhaser;
use Stu\Module\Ship\Action\DeactivateShields\DeactivateShields;
use Stu\Module\Ship\Action\DeactivateTorpedo\DeactivateTorpedo;
use Stu\Module\Ship\Action\DeactivateTractorBeam\DeactivateTractorBeam;
use Stu\Module\Ship\Action\DeactivateWarp\DeactivateWarp;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleet;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleetRequest;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleetRequestInterface;
use Stu\Module\Ship\Action\DisplayNotOwner\DisplayNotOwner;
use Stu\Module\Ship\Action\DockShip\DockShip;
use Stu\Module\Ship\Action\DoTachyonScan\DoTachyonScan;
use Stu\Module\Ship\Action\DumpForeignCrewman\DumpForeignCrewman;
use Stu\Module\Ship\Action\EnterStarSystem\EnterStarSystem;
use Stu\Module\Ship\Action\EpsTransfer\EpsTransfer;
use Stu\Module\Ship\Action\EscapeTractorBeam\EscapeTractorBeam;
use Stu\Module\Ship\Action\FleetActivateCloak\FleetActivateCloak;
use Stu\Module\Ship\Action\FleetActivateNbs\FleetActivateNbs;
use Stu\Module\Ship\Action\FleetActivatePhaser\FleetActivatePhaser;
use Stu\Module\Ship\Action\FleetActivateShields\FleetActivateShields;
use Stu\Module\Ship\Action\FleetActivateTorpedo\FleetActivateTorpedo;
use Stu\Module\Ship\Action\FleetActivateWarp\FleetActivateWarp;
use Stu\Module\Ship\Action\FleetAlertGreen\FleetAlertGreen;
use Stu\Module\Ship\Action\FleetAlertRed\FleetAlertRed;
use Stu\Module\Ship\Action\FleetAlertYellow\FleetAlertYellow;
use Stu\Module\Ship\Action\FleetDeactivateCloak\FleetDeactivateCloak;
use Stu\Module\Ship\Action\FleetDeactivateNbs\FleetDeactivateNbs;
use Stu\Module\Ship\Action\FleetDeactivatePhaser\FleetDeactivatePhaser;
use Stu\Module\Ship\Action\FleetDeactivateShields\FleetDeactivateShields;
use Stu\Module\Ship\Action\FleetDeactivateTorpedo\FleetDeactivateTorpedo;
use Stu\Module\Ship\Action\FleetDeactivateWarp\FleetDeactivateWarp;
use Stu\Module\Ship\Action\HideFleet\HideFleet;
use Stu\Module\Ship\Action\InterceptShip\InterceptShip;
use Stu\Module\Ship\Action\JoinFleet\JoinFleetFromNbs;
use Stu\Module\Ship\Action\JoinFleet\JoinFleetInShiplist;
use Stu\Module\Ship\Action\LandShuttle\LandShuttle;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleet;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleetRequest;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleetRequestInterface;
use Stu\Module\Ship\Action\LeaveStarSystem\LeaveStarSystem;
use Stu\Module\Ship\Action\TroopTransfer\TroopTransfer;
use Stu\Module\Ship\Action\LoadReactor\LoadReactor;
use Stu\Module\Ship\Action\MoveShip\MoveShip;
use Stu\Module\Ship\Action\MoveShipDown\MoveShipDown;
use Stu\Module\Ship\Action\MoveShipLeft\MoveShipLeft;
use Stu\Module\Ship\Action\MoveShipRight\MoveShipRight;
use Stu\Module\Ship\Action\MoveShipUp\MoveShipUp;
use Stu\Module\Ship\Action\PriorizeFleet\PriorizeFleet;
use Stu\Module\Ship\Action\PriorizeFleet\PriorizeFleetRequest;
use Stu\Module\Ship\Action\PriorizeFleet\PriorizeFleetRequestInterface;
use Stu\Module\Ship\Action\RenameCrew\RenameCrew;
use Stu\Module\Ship\Action\RenameFleet\RenameFleet;
use Stu\Module\Ship\Action\RenameFleet\RenameFleetRequest;
use Stu\Module\Ship\Action\RenameFleet\RenameFleetRequestInterface;
use Stu\Module\Ship\Action\SalvageEmergencyPods\SalvageEmergencyPods;
use Stu\Module\Ship\Action\SelfDestruct\SelfDestruct;
use Stu\Module\Ship\Action\Selfrepair\Selfrepair;
use Stu\Module\Ship\Action\SetLSSModeBorder\SetLSSModeBorder;
use Stu\Module\Ship\Action\SetLSSModeNormal\SetLSSModeNormal;
use Stu\Module\Ship\Action\SetGreenAlert\SetGreenAlert;
use Stu\Module\Ship\Action\SetRedAlert\SetRedAlert;
use Stu\Module\Ship\Action\SetYellowAlert\SetYellowAlert;
use Stu\Module\Ship\Action\ShowFleet\ShowFleet;
use Stu\Module\Ship\Action\Shutdown\Shutdown;
use Stu\Module\Ship\Action\StartShuttle\StartShuttle;
use Stu\Module\Ship\Action\StoreShuttle\StoreShuttle;
use Stu\Module\Ship\Action\TorpedoTransfer\TorpedoTransfer;
use Stu\Module\Ship\Action\TransferFromAccount\TransferFromAccount;
use Stu\Module\Ship\Action\TransferToAccount\TransferToAccount;
use Stu\Module\Ship\Action\Transwarp\Transwarp;
use Stu\Module\Ship\Action\UndockShip\UndockShip;
use Stu\Module\Ship\Action\UnloadBattery\UnloadBattery;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefend;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\DockPrivilegeUtility;
use Stu\Module\Ship\Lib\DockPrivilegeUtilityInterface;
use Stu\Module\Ship\Lib\ShipCreator;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoader;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMover;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipLeaver;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Module\Ship\Lib\ShipRemover;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\TroopTransferUtility;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ReactorUtil;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ShipMover2;
use Stu\Module\Ship\Lib\ShipMover2Interface;
use Stu\Module\Ship\View\Noop\Noop;
use Stu\Module\Ship\View\Overview\Overview;
use Stu\Module\Ship\View\ShowAlertLevel\ShowAlertLevel;
use Stu\Module\Ship\View\ShowAstroEntry\ShowAstroEntry;
use Stu\Module\Ship\View\ShowBeamFrom\ShowBeamFrom;
use Stu\Module\Ship\View\ShowBeamFromColony\ShowBeamFromColony;
use Stu\Module\Ship\View\ShowBeamTo\ShowBeamTo;
use Stu\Module\Ship\View\ShowBeamToColony\ShowBeamToColony;
use Stu\Module\Ship\View\ShowColonization\ShowColonization;
use Stu\Module\Ship\View\ShowColonyScan\ShowColonyScan;
use Stu\Module\Ship\View\ShowEpsTransfer\ShowEpsTransfer;
use Stu\Module\Ship\View\ShowInformation\ShowInformation;
use Stu\Module\Ship\View\ShowRegionInfo\ShowRegionInfo;
use Stu\Module\Ship\View\ShowRenameCrew\ShowRenameCrew;
use Stu\Module\Ship\View\ShowRepairOptions\ShowRepairOptions;
use Stu\Module\Ship\View\ShowScan\ShowScan;
use Stu\Module\Ship\View\ShowSectorScan\ShowSectorScan;
use Stu\Module\Ship\View\ShowSelfDestruct\ShowSelfDestruct;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\View\ShowShipDetails\ShowShipDetails;
use Stu\Module\Ship\View\ShowShiplistFleet\ShowShiplistFleet;
use Stu\Module\Ship\View\ShowShipStorage\ShowShipStorage;
use Stu\Module\Ship\View\ShowTorpedoTransfer\ShowTorpedoTransfer;
use Stu\Module\Ship\View\ShowTradeMenu\ShowTradeMenu;
use Stu\Module\Ship\View\ShowTradeMenuPayment\ShowTradeMenuPayment;
use Stu\Module\Ship\View\ShowTradeMenuTransfer\ShowTradeMenuTransfer;
use Stu\Module\Ship\View\ShowTroopTransfer\ShowTroopTransfer;

use function DI\autowire;

return [
    ShipMoverInterface::class => autowire(ShipMover::class),
    ShipMover2Interface::class => autowire(ShipMover2::class),
    ModuleValueCalculatorInterface::class => autowire(ModuleValueCalculator::class),
    PositionCheckerInterface::class => autowire(PositionChecker::class),
    RenameCrewRequestInterface::class => autowire(RenameCrewRequest::class),
    ChangeNameRequestInterface::class => autowire(ChangeNameRequest::class),
    ApplyDamageInterface::class => autowire(ApplyDamage::class),
    EnergyWeaponPhaseInterface::class => autowire(EnergyWeaponPhase::class),
    FightLibInterface::class => autowire(FightLib::class),
    ProjectileWeaponPhaseInterface::class => autowire(ProjectileWeaponPhase::class),
    ShipAttackCycleInterface::class => autowire(ShipAttackCycle::class),
    ActivatorDeactivatorHelperInterface::class => autowire(ActivatorDeactivatorHelper::class),
    AlertRedHelperInterface::class => autowire(AlertRedHelper::class),
    AstroEntryLibInterface::class => autowire(AstroEntryLib::class),
    ShipLeaverInterface::class => autowire(ShipLeaver::class),
    TroopTransferUtilityInterface::class => autowire(TroopTransferUtility::class),
    DockPrivilegeUtilityInterface::class => autowire(DockPrivilegeUtility::class),
    CancelColonyBlockOrDefendInterface::class => autowire(CancelColonyBlockOrDefend::class),
    ShipRemoverInterface::class => autowire(ShipRemover::class),
    ShipCreatorInterface::class => autowire(ShipCreator::class),
    CreateFleetRequestInterface::class => autowire(CreateFleetRequest::class),
    DeleteFleetRequestInterface::class => autowire(DeleteFleetRequest::class),
    RenameFleetRequestInterface::class => autowire(RenameFleetRequest::class),
    LeaveFleetRequestInterface::class => autowire(LeaveFleetRequest::class),
    ShipLoaderInterface::class => autowire(ShipLoader::class),
    PriorizeFleetRequestInterface::class => autowire(PriorizeFleetRequest::class),
    ReactorUtilInterface::class => autowire(ReactorUtil::class),
    'SHIP_ACTIONS' => [
        DisplayNotOwner::ACTION_IDENTIFIER => autowire(DisplayNotOwner::class),
        CreateFleet::ACTION_IDENTIFIER => autowire(CreateFleet::class),
        DeleteFleet::ACTION_IDENTIFIER => autowire(DeleteFleet::class),
        RenameFleet::ACTION_IDENTIFIER => autowire(RenameFleet::class),
        LeaveFleet::ACTION_IDENTIFIER => autowire(LeaveFleet::class),
        JoinFleetFromNbs::ACTION_IDENTIFIER => autowire(JoinFleetFromNbs::class),
        JoinFleetInShiplist::ACTION_IDENTIFIER => autowire(JoinFleetInShiplist::class),
        PriorizeFleet::ACTION_IDENTIFIER => autowire(PriorizeFleet::class),
        ChangeFleetFleader::ACTION_IDENTIFIER => autowire(ChangeFleetFleader::class),
        ActivateAstroLaboratory::ACTION_IDENTIFIER => autowire(ActivateAstroLaboratory::class),
        DeactivateAstroLaboratory::ACTION_IDENTIFIER => autowire(DeactivateAstroLaboratory::class),
        ActivateCloak::ACTION_IDENTIFIER => autowire(ActivateCloak::class),
        ActivateSubspace::ACTION_IDENTIFIER => autowire(ActivateSubspace::class),
        ActivateTachyon::ACTION_IDENTIFIER => autowire(ActivateTachyon::class),
        ActivateUplink::ACTION_IDENTIFIER => autowire(ActivateUplink::class),
        DeactivateCloak::ACTION_IDENTIFIER => autowire(DeactivateCloak::class),
        DeactivateSubspace::ACTION_IDENTIFIER => autowire(DeactivateSubspace::class),
        DeactivateTachyon::ACTION_IDENTIFIER => autowire(DeactivateTachyon::class),
        ActivateLss::ACTION_IDENTIFIER => autowire(ActivateLss::class),
        DeactivateLss::ACTION_IDENTIFIER => autowire(DeactivateLss::class),
        ActivateNbs::ACTION_IDENTIFIER => autowire(ActivateNbs::class),
        DeactivateNbs::ACTION_IDENTIFIER => autowire(DeactivateNbs::class),
        ActivateShields::ACTION_IDENTIFIER => autowire(ActivateShields::class),
        DeactivateShields::ACTION_IDENTIFIER => autowire(DeactivateShields::class),
        ActivatePhaser::ACTION_IDENTIFIER => autowire(ActivatePhaser::class),
        DeactivatePhaser::ACTION_IDENTIFIER => autowire(DeactivatePhaser::class),
        ActivateTorpedo::ACTION_IDENTIFIER => autowire(ActivateTorpedo::class),
        DeactivateTorpedo::ACTION_IDENTIFIER => autowire(DeactivateTorpedo::class),
        ChangeName::ACTION_IDENTIFIER => autowire(ChangeName::class),
        LeaveStarSystem::ACTION_IDENTIFIER => autowire(LeaveStarSystem::class),
        EnterStarSystem::ACTION_IDENTIFIER => autowire(EnterStarSystem::class),
        MoveShip::ACTION_IDENTIFIER => autowire(MoveShip::class),
        MoveShipUp::ACTION_IDENTIFIER => autowire(MoveShipUp::class),
        MoveShipDown::ACTION_IDENTIFIER => autowire(MoveShipDown::class),
        MoveShipLeft::ACTION_IDENTIFIER => autowire(MoveShipLeft::class),
        MoveShipRight::ACTION_IDENTIFIER => autowire(MoveShipRight::class),
        ActivateWarp::ACTION_IDENTIFIER => autowire(ActivateWarp::class),
        DeactivateWarp::ACTION_IDENTIFIER => autowire(DeactivateWarp::class),
        UnloadBattery::ACTION_IDENTIFIER => autowire(UnloadBattery::class),
        ActivateTractorBeam::ACTION_IDENTIFIER => autowire(ActivateTractorBeam::class),
        DeactivateTractorBeam::ACTION_IDENTIFIER => autowire(DeactivateTractorBeam::class),
        SetGreenAlert::ACTION_IDENTIFIER => autowire(SetGreenAlert::class),
        SetYellowAlert::ACTION_IDENTIFIER => autowire(SetYellowAlert::class),
        SetRedAlert::ACTION_IDENTIFIER => autowire(SetRedAlert::class),
        EpsTransfer::ACTION_IDENTIFIER => autowire(EpsTransfer::class),
        TorpedoTransfer::ACTION_IDENTIFIER => autowire(TorpedoTransfer::class),
        BeamTo::ACTION_IDENTIFIER => autowire(BeamTo::class),
        BeamFrom::ACTION_IDENTIFIER => autowire(BeamFrom::class),
        BeamToColony::ACTION_IDENTIFIER => autowire(BeamToColony::class),
        BeamFromColony::ACTION_IDENTIFIER => autowire(BeamFromColony::class),
        SelfDestruct::ACTION_IDENTIFIER => autowire(SelfDestruct::class),
        AttackBuilding::ACTION_IDENTIFIER => autowire(AttackBuilding::class),
        AttackShip::ACTION_IDENTIFIER => autowire(AttackShip::class),
        InterceptShip::ACTION_IDENTIFIER => autowire(InterceptShip::class),
        DockShip::ACTION_IDENTIFIER => autowire(DockShip::class),
        DoTachyonScan::ACTION_IDENTIFIER => autowire(DoTachyonScan::class),
        UndockShip::ACTION_IDENTIFIER => autowire(UndockShip::class),
        BuyTradeLicense::ACTION_IDENTIFIER => autowire(BuyTradeLicense::class),
        TransferToAccount::ACTION_IDENTIFIER => autowire(TransferToAccount::class),
        TransferFromAccount::ACTION_IDENTIFIER => autowire(TransferFromAccount::class),
        HideFleet::ACTION_IDENTIFIER => autowire(HideFleet::class),
        ShowFleet::ACTION_IDENTIFIER => autowire(ShowFleet::class),
        FleetActivateNbs::ACTION_IDENTIFIER => autowire(FleetActivateNbs::class),
        FleetDeactivateNbs::ACTION_IDENTIFIER => autowire(FleetDeactivateNbs::class),
        FleetActivateShields::ACTION_IDENTIFIER => autowire(FleetActivateShields::class),
        FleetDeactivateShields::ACTION_IDENTIFIER => autowire(FleetDeactivateShields::class),
        FleetActivatePhaser::ACTION_IDENTIFIER => autowire(FleetActivatePhaser::class),
        FleetDeactivatePhaser::ACTION_IDENTIFIER => autowire(FleetDeactivatePhaser::class),
        FleetActivateTorpedo::ACTION_IDENTIFIER => autowire(FleetActivateTorpedo::class),
        FleetDeactivateTorpedo::ACTION_IDENTIFIER => autowire(FleetDeactivateTorpedo::class),
        FleetActivateCloak::ACTION_IDENTIFIER => autowire(FleetActivateCloak::class),
        FleetDeactivateCloak::ACTION_IDENTIFIER => autowire(FleetDeactivateCloak::class),
        FleetActivateWarp::ACTION_IDENTIFIER => autowire(FleetActivateWarp::class),
        FleetDeactivateWarp::ACTION_IDENTIFIER => autowire(FleetDeactivateWarp::class),
        FleetAlertGreen::ACTION_IDENTIFIER => autowire(FleetAlertGreen::class),
        FleetAlertYellow::ACTION_IDENTIFIER => autowire(FleetAlertYellow::class),
        FleetAlertRed::ACTION_IDENTIFIER => autowire(FleetAlertRed::class),
        LoadReactor::ACTION_IDENTIFIER => autowire(LoadReactor::class),
        EscapeTractorBeam::ACTION_IDENTIFIER => autowire(EscapeTractorBeam::class),
        Colonize::ACTION_IDENTIFIER => autowire(Colonize::class),
        RenameCrew::ACTION_IDENTIFIER => autowire(RenameCrew::class),
        SalvageEmergencyPods::ACTION_IDENTIFIER => autowire(SalvageEmergencyPods::class),
        TroopTransfer::ACTION_IDENTIFIER => autowire(TroopTransfer::class),
        StartDefending::ACTION_IDENTIFIER => autowire(StartDefending::class),
        StopDefending::ACTION_IDENTIFIER => autowire(StopDefending::class),
        StartBlocking::ACTION_IDENTIFIER => autowire(StartBlocking::class),
        StopBlocking::ACTION_IDENTIFIER => autowire(StopBlocking::class),
        PlanAstroMapping::ACTION_IDENTIFIER => autowire(PlanAstroMapping::class),
        StartAstroMapping::ACTION_IDENTIFIER => autowire(StartAstroMapping::class),
        Shutdown::ACTION_IDENTIFIER => autowire(Shutdown::class),
        StartShuttle::ACTION_IDENTIFIER => autowire(StartShuttle::class),
        StoreShuttle::ACTION_IDENTIFIER => autowire(StoreShuttle::class),
        LandShuttle::ACTION_IDENTIFIER => autowire(LandShuttle::class),
        BuildConstruction::ACTION_IDENTIFIER => autowire(BuildConstruction::class),
        ChangeFleetFixation::ACTION_IDENTIFIER => autowire(ChangeFleetFixation::class),
        DumpForeignCrewman::ACTION_IDENTIFIER => autowire(DumpForeignCrewman::class),
        Selfrepair::ACTION_IDENTIFIER => autowire(Selfrepair::class),
        SetLSSModeNormal::ACTION_IDENTIFIER => autowire(SetLSSModeNormal::class),
        SetLSSModeBorder::ACTION_IDENTIFIER => autowire(SetLSSModeBorder::class),
        Transwarp::ACTION_IDENTIFIER => autowire(Transwarp::class)
    ],
    'SHIP_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Overview::VIEW_IDENTIFIER => autowire(Overview::class),
        ShowShip::VIEW_IDENTIFIER => autowire(ShowShip::class),
        ShowAlertLevel::VIEW_IDENTIFIER => autowire(ShowAlertLevel::class),
        ShowAstroEntry::VIEW_IDENTIFIER => autowire(ShowAstroEntry::class),
        ShowEpsTransfer::VIEW_IDENTIFIER => autowire(ShowEpsTransfer::class),
        ShowBeamTo::VIEW_IDENTIFIER => autowire(ShowBeamTo::class),
        ShowBeamFrom::VIEW_IDENTIFIER => autowire(ShowBeamFrom::class),
        ShowBeamToColony::VIEW_IDENTIFIER => autowire(ShowBeamToColony::class),
        ShowBeamFromColony::VIEW_IDENTIFIER => autowire(ShowBeamFromColony::class),
        ShowSelfDestruct::VIEW_IDENTIFIER => autowire(ShowSelfDestruct::class),
        ShowScan::VIEW_IDENTIFIER => autowire(ShowScan::class),
        ShowColonyScan::VIEW_IDENTIFIER => autowire(ShowColonyScan::class),
        ShowSectorScan::VIEW_IDENTIFIER => autowire(ShowSectorScan::class),
        ShowShipDetails::VIEW_IDENTIFIER => autowire(ShowShipDetails::class),
        ShowShipStorage::VIEW_IDENTIFIER => autowire(ShowShipStorage::class),
        ShowTroopTransfer::VIEW_IDENTIFIER => autowire(ShowTroopTransfer::class),
        ShowTorpedoTransfer::VIEW_IDENTIFIER => autowire(ShowTorpedoTransfer::class),
        ShowTradeMenu::VIEW_IDENTIFIER => autowire(ShowTradeMenu::class),
        ShowTradeMenuPayment::VIEW_IDENTIFIER => autowire(ShowTradeMenuPayment::class),
        ShowTradeMenuTransfer::VIEW_IDENTIFIER => autowire(ShowTradeMenuTransfer::class),
        ShowRegionInfo::VIEW_IDENTIFIER => autowire(ShowRegionInfo::class),
        ShowColonization::VIEW_IDENTIFIER => autowire(ShowColonization::class),
        ShowRenameCrew::VIEW_IDENTIFIER => autowire(ShowRenameCrew::class),
        ShowRepairOptions::VIEW_IDENTIFIER => autowire(ShowRepairOptions::class),
        ShowInformation::VIEW_IDENTIFIER => autowire(ShowInformation::class),
        ShowShiplistFleet::VIEW_IDENTIFIER => autowire(ShowShiplistFleet::class),
        Noop::VIEW_IDENTIFIER => autowire(Noop::class)
    ],
];
