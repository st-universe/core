<?php

declare(strict_types=1);

namespace Stu\Module\Ship;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Ship\Action\ActivateAstroLaboratory\ActivateAstroLaboratory;
use Stu\Module\Ship\Action\ActivateUplink\ActivateUplink;
use Stu\Module\Ship\Action\AstroMapping\PlanAstroMapping;
use Stu\Module\Ship\Action\AstroMapping\StartAstroMapping;
use Stu\Module\Ship\Action\AttackTrackedShip\AttackTrackedShip;
use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Module\Ship\Action\BuyTradeLicense\BuyTradeLicense;
use Stu\Module\Ship\Action\ChangeFleetFixation\ChangeFleetFixation;
use Stu\Module\Ship\Action\ChangeFleetFleader\ChangeFleetFleader;
use Stu\Module\Ship\Action\Colonize\Colonize;
use Stu\Module\Ship\Action\ColonyBlocking\StartBlocking;
use Stu\Module\Ship\Action\ColonyBlocking\StopBlocking;
use Stu\Module\Ship\Action\ColonyDefending\StartDefending;
use Stu\Module\Ship\Action\ColonyDefending\StopDefending;
use Stu\Module\Ship\Action\CreateFleet\CreateFleet;
use Stu\Module\Ship\Action\CreateFleet\CreateFleetRequest;
use Stu\Module\Ship\Action\CreateFleet\CreateFleetRequestInterface;
use Stu\Module\Ship\Action\DeactivateAstroLaboratory\DeactivateAstroLaboratory;
use Stu\Module\Ship\Action\DeactivateTrackingDevice\DeactivateTrackingDevice;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleet;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleetRequest;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleetRequestInterface;
use Stu\Module\Ship\Action\DisplayNotOwner\DisplayNotOwner;
use Stu\Module\Ship\Action\DockShip\DockShip;
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
use Stu\Module\Ship\Action\JoinFleet\JoinFleetFromNbs;
use Stu\Module\Ship\Action\JoinFleet\JoinFleetInShiplist;
use Stu\Module\Ship\Action\LandShuttle\LandShuttle;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleet;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleetRequest;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleetRequestInterface;
use Stu\Module\Ship\Action\Mining\GatherResources;
use Stu\Module\Ship\Action\PriorizeFleet\PriorizeFleet;
use Stu\Module\Ship\Action\PriorizeFleet\PriorizeFleetRequest;
use Stu\Module\Ship\Action\PriorizeFleet\PriorizeFleetRequestInterface;
use Stu\Module\Ship\Action\RenameFleet\RenameFleet;
use Stu\Module\Ship\Action\RenameFleet\RenameFleetRequest;
use Stu\Module\Ship\Action\RenameFleet\RenameFleetRequestInterface;
use Stu\Module\Ship\Action\SalvageCrew\SalvageCrew;
use Stu\Module\Ship\Action\ShowFleet\ShowFleet;
use Stu\Module\Ship\Action\StoreShuttle\StoreShuttle;
use Stu\Module\Ship\Action\TholianWeb\CancelTholianWeb;
use Stu\Module\Ship\Action\TholianWeb\CreateTholianWeb;
use Stu\Module\Ship\Action\TholianWeb\ImplodeTholianWeb;
use Stu\Module\Ship\Action\TholianWeb\RemoveTholianWeb;
use Stu\Module\Ship\Action\TholianWeb\SupportTholianWeb;
use Stu\Module\Ship\Action\TholianWeb\UnsupportTholianWeb;
use Stu\Module\Ship\Action\ToggleFleetVisibility\ToggleFleetVisibility;
use Stu\Module\Ship\Action\TrackShip\TrackShip;
use Stu\Module\Ship\Action\TransferFromAccount\TransferFromAccount;
use Stu\Module\Ship\Action\TransferToAccount\TransferToAccount;
use Stu\Module\Ship\Action\Transwarp\Transwarp;
use Stu\Module\Ship\Action\UndockShip\UndockShip;
use Stu\Module\Ship\Lib\AstroEntryLib;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefend;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\Fleet\ChangeFleetLeader;
use Stu\Module\Ship\Lib\Fleet\ChangeFleetLeaderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleet as FleetLeaveFleet;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipCreator;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoader;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRetrofit;
use Stu\Module\Ship\Lib\ShipRetrofitInterface;
use Stu\Module\Ship\Lib\TholianWebUtil;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Ship\View\ShowAstroEntry\ShowAstroEntry;
use Stu\Module\Ship\View\ShowAvailableShips\ShowAvailableShips;
use Stu\Module\Ship\View\ShowBuoyList\ShowBuoyList;
use Stu\Module\Ship\View\ShowBussardCollector\ShowBussardCollector;
use Stu\Module\Ship\View\ShowColonization\ShowColonization;
use Stu\Module\Ship\View\ShowShiplistFleet\ShowShiplistFleet;
use Stu\Module\Ship\View\ShowShiplistSingles\ShowShiplistSingles;
use Stu\Module\Ship\View\ShowTradeMenu\ShowTradeMenu;
use Stu\Module\Ship\View\ShowTradeMenuPayment\ShowTradeMenuPayment;
use Stu\Module\Ship\View\ShowTradeMenuTransfer\ShowTradeMenuTransfer;
use Stu\Module\Ship\View\ShowWebEmitter\ShowWebEmitter;

use function DI\autowire;

return [
    AstroEntryLibInterface::class => autowire(AstroEntryLib::class),
    CancelColonyBlockOrDefendInterface::class => autowire(CancelColonyBlockOrDefend::class),
    ChangeFleetLeaderInterface::class => autowire(ChangeFleetLeader::class),
    CreateFleetRequestInterface::class => autowire(CreateFleetRequest::class),
    DeleteFleetRequestInterface::class => autowire(DeleteFleetRequest::class),
    LeaveFleetInterface::class => autowire(FleetLeaveFleet::class),
    LeaveFleetRequestInterface::class => autowire(LeaveFleetRequest::class),
    PriorizeFleetRequestInterface::class => autowire(PriorizeFleetRequest::class),
    RenameFleetRequestInterface::class => autowire(RenameFleetRequest::class),
    ShipCreatorInterface::class => autowire(ShipCreator::class),
    ShipLoaderInterface::class => autowire(ShipLoader::class),
    ShipRetrofitInterface::class => autowire(ShipRetrofit::class),
    TholianWebUtilInterface::class => autowire(TholianWebUtil::class),
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
        ActivateUplink::ACTION_IDENTIFIER => autowire(ActivateUplink::class),
        GatherResources::ACTION_IDENTIFIER => autowire(GatherResources::class),
        UndockShip::ACTION_IDENTIFIER => autowire(UndockShip::class),
        DockShip::ACTION_IDENTIFIER => autowire(DockShip::class),
        AttackTrackedShip::ACTION_IDENTIFIER => autowire(AttackTrackedShip::class),
        BuyTradeLicense::ACTION_IDENTIFIER => autowire(BuyTradeLicense::class),
        DeactivateTrackingDevice::ACTION_IDENTIFIER => autowire(DeactivateTrackingDevice::class),
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
        EscapeTractorBeam::ACTION_IDENTIFIER => autowire(EscapeTractorBeam::class),
        Colonize::ACTION_IDENTIFIER => autowire(Colonize::class),
        StartDefending::ACTION_IDENTIFIER => autowire(StartDefending::class),
        StopDefending::ACTION_IDENTIFIER => autowire(StopDefending::class),
        StartBlocking::ACTION_IDENTIFIER => autowire(StartBlocking::class),
        StopBlocking::ACTION_IDENTIFIER => autowire(StopBlocking::class),
        PlanAstroMapping::ACTION_IDENTIFIER => autowire(PlanAstroMapping::class),
        StartAstroMapping::ACTION_IDENTIFIER => autowire(StartAstroMapping::class),
        StoreShuttle::ACTION_IDENTIFIER => autowire(StoreShuttle::class),
        LandShuttle::ACTION_IDENTIFIER => autowire(LandShuttle::class),
        BuildConstruction::ACTION_IDENTIFIER => autowire(BuildConstruction::class),
        ChangeFleetFixation::ACTION_IDENTIFIER => autowire(ChangeFleetFixation::class),
        Transwarp::ACTION_IDENTIFIER => autowire(Transwarp::class),
        ToggleFleetVisibility::ACTION_IDENTIFIER => autowire(ToggleFleetVisibility::class),
        SalvageCrew::ACTION_IDENTIFIER => autowire(SalvageCrew::class),
        TrackShip::ACTION_IDENTIFIER => autowire(TrackShip::class),
        CreateTholianWeb::ACTION_IDENTIFIER => autowire(CreateTholianWeb::class),
        CancelTholianWeb::ACTION_IDENTIFIER => autowire(CancelTholianWeb::class),
        ImplodeTholianWeb::ACTION_IDENTIFIER => autowire(ImplodeTholianWeb::class),
        RemoveTholianWeb::ACTION_IDENTIFIER => autowire(RemoveTholianWeb::class),
        SupportTholianWeb::ACTION_IDENTIFIER => autowire(SupportTholianWeb::class),
        UnsupportTholianWeb::ACTION_IDENTIFIER => autowire(UnsupportTholianWeb::class),
    ],
    'SHIP_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowAstroEntry::VIEW_IDENTIFIER => autowire(ShowAstroEntry::class),
        ShowBussardCollector::VIEW_IDENTIFIER => autowire(ShowBussardCollector::class),
        ShowTradeMenu::VIEW_IDENTIFIER => autowire(ShowTradeMenu::class),
        ShowTradeMenuPayment::VIEW_IDENTIFIER => autowire(ShowTradeMenuPayment::class),
        ShowTradeMenuTransfer::VIEW_IDENTIFIER => autowire(ShowTradeMenuTransfer::class),
        ShowColonization::VIEW_IDENTIFIER => autowire(ShowColonization::class),
        ShowShiplistFleet::VIEW_IDENTIFIER => autowire(ShowShiplistFleet::class),
        ShowShiplistSingles::VIEW_IDENTIFIER => autowire(ShowShiplistSingles::class),
        ShowAvailableShips::VIEW_IDENTIFIER => autowire(ShowAvailableShips::class),
        ShowBuoyList::VIEW_IDENTIFIER => autowire(ShowBuoyList::class),
        ShowWebEmitter::VIEW_IDENTIFIER => autowire(ShowWebEmitter::class),
    ]
];
