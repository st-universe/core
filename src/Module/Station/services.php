<?php

declare(strict_types=1);

namespace Stu\Module\Station;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Spacecraft\Action\AttackSpacecraft\AttackSpacecraft;
use Stu\Module\Station\Action\ActivateConstructionHub\ActivateConstructionHub;
use Stu\Module\Station\Action\AddDockPrivilege\AddDockPrivilege;
use Stu\Module\Station\Action\BuildShipyardShip\BuildShipyardShip;
use Stu\Module\Station\Action\BuildStation\BuildStation;
use Stu\Module\Station\Action\CancelShipRepair\CancelShipRepair;
use Stu\Module\Station\Action\DeactivateConstructionHub\DeactivateConstructionHub;
use Stu\Module\Station\Action\DeleteDockPrivilege\DeleteDockPrivilege;
use Stu\Module\Station\Action\DockFleet\DockFleet;
use Stu\Module\Station\Action\DockTractoredShip\DockTractoredShip;
use Stu\Module\Station\Action\ManageShips\ManageShips;
use Stu\Module\Station\Action\ManageShuttles\ManageShuttles;
use Stu\Module\Station\Action\RepairShip\RepairShip;
use Stu\Module\Station\Action\Scrapping\Scrapping;
use Stu\Module\Station\Action\StationRepair\StationRepair;
use Stu\Module\Station\Action\ToggleBatteryReload\ToggleBatteryReload;
use Stu\Module\Station\Action\ToggleDockPmAutoRead\ToggleDockPmAutoRead;
use Stu\Module\Station\Action\TransformResources\TransformResources;
use Stu\Module\Station\Action\UndockStationShip\UndockStationShip;
use Stu\Module\Station\Lib\Creation\StationCreator;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Module\Station\Lib\StationLoader;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\Lib\StationUiFactory;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Module\Station\View\ShowAggregationSystem\ShowAggregationSystem;
use Stu\Module\Station\View\ShowDockingControl\ShowDockingControl;
use Stu\Module\Station\View\ShowDockingPrivileges\ShowDockingPrivileges;
use Stu\Module\Station\View\ShowScrapping\ShowScrapping;
use Stu\Module\Station\View\ShowSensorScan\ShowSensorScan;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagementRequest;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagementRequestInterface;
use Stu\Module\Station\View\ShowShipRepair\ShowShipRepair;
use Stu\Module\Station\View\ShowShuttleManagement\ShowShuttleManagement;
use Stu\Module\Station\View\ShowShuttleManagement\ShowShuttleManagementRequest;
use Stu\Module\Station\View\ShowShuttleManagement\ShowShuttleManagementRequestInterface;
use Stu\Module\Station\View\ShowStationCosts\ShowStationCosts;
use Stu\Module\Station\View\ShowStationInfo\ShowStationInfo;
use Stu\Module\Station\View\ShowStationShiplist\ShowStationShiplist;
use Stu\Module\Station\View\ShowStationShiplist\ShowStationShiplistRequest;
use Stu\Module\Station\View\ShowStationShiplist\ShowStationShiplistRequestInterface;
use Stu\Module\Station\View\ShowSystemSensorScan\ShowSystemSensorScan;

use function DI\autowire;

return [
    StationLoaderInterface::class => autowire(StationLoader::class),
    StationCreatorInterface::class => autowire(StationCreator::class),
    ShowShipManagementRequestInterface::class => autowire(ShowShipManagementRequest::class),
    ShowStationShiplistRequestInterface::class => autowire(ShowStationShiplistRequest::class),
    ShowShuttleManagementRequestInterface::class => autowire(ShowShuttleManagementRequest::class),
    'STATION_ACTIONS' => [
        AttackSpacecraft::ACTION_IDENTIFIER => autowire(AttackSpacecraft::class),
        BuildStation::ACTION_IDENTIFIER => autowire(BuildStation::class),
        BuildShipyardShip::ACTION_IDENTIFIER => autowire(BuildShipyardShip::class),
        ManageShips::ACTION_IDENTIFIER => autowire(ManageShips::class),
        ManageShuttles::ACTION_IDENTIFIER => autowire(ManageShuttles::class),
        AddDockPrivilege::ACTION_IDENTIFIER => autowire(AddDockPrivilege::class),
        DeleteDockPrivilege::ACTION_IDENTIFIER => autowire(DeleteDockPrivilege::class),
        UndockStationShip::ACTION_IDENTIFIER => autowire(UndockStationShip::class),
        ActivateConstructionHub::ACTION_IDENTIFIER => autowire(ActivateConstructionHub::class),
        DeactivateConstructionHub::ACTION_IDENTIFIER => autowire(DeactivateConstructionHub::class),
        RepairShip::ACTION_IDENTIFIER => autowire(RepairShip::class),
        CancelShipRepair::ACTION_IDENTIFIER => autowire(CancelShipRepair::class),
        DockFleet::ACTION_IDENTIFIER => autowire(DockFleet::class),
        DockTractoredShip::ACTION_IDENTIFIER => autowire(DockTractoredShip::class),
        Scrapping::ACTION_IDENTIFIER => autowire(Scrapping::class),
        StationRepair::ACTION_IDENTIFIER => autowire(StationRepair::class),
        ToggleBatteryReload::ACTION_IDENTIFIER => autowire(ToggleBatteryReload::class),
        ToggleDockPmAutoRead::ACTION_IDENTIFIER => autowire(ToggleDockPmAutoRead::class),
        TransformResources::ACTION_IDENTIFIER => autowire(TransformResources::class),
    ],
    'STATION_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowAggregationSystem::VIEW_IDENTIFIER => autowire(ShowAggregationSystem::class),
        ShowDockingControl::VIEW_IDENTIFIER => autowire(ShowDockingControl::class),
        ShowDockingPrivileges::VIEW_IDENTIFIER => autowire(ShowDockingPrivileges::class),
        ShowStationCosts::VIEW_IDENTIFIER => autowire(ShowStationCosts::class),
        ShowSensorScan::VIEW_IDENTIFIER => autowire(ShowSensorScan::class),
        ShowStationInfo::VIEW_IDENTIFIER => autowire(ShowStationInfo::class),
        ShowShipManagement::VIEW_IDENTIFIER => autowire(ShowShipManagement::class),
        ShowStationShiplist::VIEW_IDENTIFIER => autowire(ShowStationShiplist::class),
        ShowShuttleManagement::VIEW_IDENTIFIER => autowire(ShowShuttleManagement::class),
        ShowSystemSensorScan::VIEW_IDENTIFIER => autowire(ShowSystemSensorScan::class),
        ShowShipRepair::VIEW_IDENTIFIER => autowire(ShowShipRepair::class),
        ShowScrapping::VIEW_IDENTIFIER => autowire(ShowScrapping::class),
    ],
    StationUiFactoryInterface::class => autowire(StationUiFactory::class),
];
