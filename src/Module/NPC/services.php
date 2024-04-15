<?php

declare(strict_types=1);

namespace Stu\Module\NPC;

use Stu\Module\NPC\View\Overview\Overview;
use Stu\Module\Control\GameController;


use function DI\autowire;
use function DI\get;

return [
    'NPC_ACTIONS' => [],
    'NPC_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class)
    ]
];