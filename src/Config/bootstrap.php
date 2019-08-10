<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\ContainerBuilder;
use Stu\Control\HistoryController;
use Stu\Control\AllianceController;
use Stu\Control\ColonyController;
use Stu\Control\ColonyListController;
use Stu\Control\CommController;
use Stu\Control\CrewController;
use Stu\Control\DatabaseController;
use Stu\Control\IndexController;
use Stu\Control\LogoutController;
use Stu\Control\MaindeskController;
use Stu\Control\NotesController;
use Stu\Control\OptionsController;
use Stu\Control\ResearchController;
use Stu\Control\ShipController;
use Stu\Control\ShiplistController;
use Stu\Control\StarmapController;
use Stu\Control\TradeController;
use Stu\Control\UserProfileController;
use function DI\create;
use Stu\Control\AdminController;

$builder = new ContainerBuilder();
$builder->addDefinitions([
    AdminController::class => create(AdminController::class),
    AllianceController::class => create(AllianceController::class),
    ColonyController::class => create(ColonyController::class),
    ColonyListController::class => create(ColonyListController::class),
    CommController::class => create(CommController::class),
    CrewController::class => create(CrewController::class),
    DatabaseController::class => create(DatabaseController::class),
    HistoryController::class => create(HistoryController::class),
    IndexController::class => create(IndexController::class),
    LogoutController::class => create(LogoutController::class),
    MaindeskController::class => create(MaindeskController::class),
    NotesController::class => create(NotesController::class),
    OptionsController::class => create(OptionsController::class),
    ResearchController::class => create(ResearchController::class),
    ShipController::class => create(ShipController::class),
    ShiplistController::class => create(ShiplistController::class),
    StarmapController::class => create(StarmapController::class),
    TradeController::class => create(TradeController::class),
    UserProfileController::class => create(UserProfileController::class),
]);
return $builder->build();
