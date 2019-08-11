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
use Stu\Lib\Session;
use function DI\create;
use function DI\get;
use Stu\Control\AdminController;

$builder = new ContainerBuilder();

$builder->addDefinitions([
    Session::class => create(Session::class),
]);

$builder->addDefinitions([
    AdminController::class => create(AdminController::class)
        ->constructor(get(Session::class)),
    AllianceController::class => create(AllianceController::class)
        ->constructor(get(Session::class)),
    ColonyController::class => create(ColonyController::class)
        ->constructor(get(Session::class)),
    ColonyListController::class => create(ColonyListController::class)
        ->constructor(get(Session::class)),
    CommController::class => create(CommController::class)
        ->constructor(get(Session::class)),
    CrewController::class => create(CrewController::class)
        ->constructor(get(Session::class)),
    DatabaseController::class => create(DatabaseController::class)
        ->constructor(get(Session::class)),
    HistoryController::class => create(HistoryController::class)
        ->constructor(get(Session::class)),
    IndexController::class => create(IndexController::class)
        ->constructor(get(Session::class)),
    LogoutController::class => create(LogoutController::class)
        ->constructor(get(Session::class)),
    MaindeskController::class => create(MaindeskController::class)
        ->constructor(get(Session::class)),
    NotesController::class => create(NotesController::class)
        ->constructor(get(Session::class)),
    OptionsController::class => create(OptionsController::class)
        ->constructor(get(Session::class)),
    ResearchController::class => create(ResearchController::class)
        ->constructor(get(Session::class)),
    ShipController::class => create(ShipController::class)
        ->constructor(get(Session::class)),
    ShiplistController::class => create(ShiplistController::class)
        ->constructor(get(Session::class)),
    StarmapController::class => create(StarmapController::class)
        ->constructor(get(Session::class)),
    TradeController::class => create(TradeController::class)
        ->constructor(get(Session::class)),
    UserProfileController::class => create(UserProfileController::class)
        ->constructor(get(Session::class)),
]);
return $builder->build();
