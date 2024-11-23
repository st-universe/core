<?php

declare(strict_types=1);

namespace Stu\Module\NPC;

use Stu\Module\Control\GameController;
use Stu\Module\NPC\Action\CommodityCheat;
use Stu\Module\NPC\View\NPCLog\NPCLog;
use Stu\Module\NPC\View\Overview\Overview;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\NPC\Action\CreateBuildplan;
use Stu\Module\NPC\View\ShowBuildplanCreator\ShowBuildplanCreator;
use Stu\Module\NPC\Action\CreateShip;
use Stu\Module\NPC\Action\DeleteBuildplan;
use Stu\Module\NPC\View\ShowShipCreator\ShowShipCreator;

use function DI\autowire;

return [
    'NPC_ACTIONS' => [
        CommodityCheat::ACTION_IDENTIFIER => autowire(CommodityCheat::class),
        CreateBuildplan::ACTION_IDENTIFIER => autowire(CreateBuildplan::class),
        CreateShip::ACTION_IDENTIFIER => autowire(CreateShip::class),
        DeleteBuildplan::ACTION_IDENTIFIER => autowire(DeleteBuildplan::class)
    ],
    'NPC_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        NPCLog::VIEW_IDENTIFIER => autowire(NPCLog::class),
        ShowTools::VIEW_IDENTIFIER => autowire(ShowTools::class),
        ShowBuildplanCreator::VIEW_IDENTIFIER => autowire(ShowBuildplanCreator::class),
        ShowShipCreator::VIEW_IDENTIFIER => autowire(ShowShipCreator::class)
    ]
];