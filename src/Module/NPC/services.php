<?php

declare(strict_types=1);

namespace Stu\Module\NPC;

use Stu\Module\NPC\Action\CommodityCheat;
use Stu\Module\NPC\View\Overview\Overview;
use Stu\Module\NPC\View\NPCLog\NPCLog;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Control\GameController;


use function DI\autowire;
use function DI\get;

return [
    'NPC_ACTIONS' => [CommodityCheat::ACTION_IDENTIFIER => autowire(CommodityCheat::class)],
    'NPC_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        NPCLog::VIEW_IDENTIFIER => autowire(NPCLog::class),
        ShowTools::VIEW_IDENTIFIER => autowire(ShowTools::class)
    ]
];
