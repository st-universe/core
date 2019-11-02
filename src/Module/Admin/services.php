<?php

declare(strict_types=1);

namespace Stu\Module\Admin;

use Stu\Module\Admin\View\Overview\Overview;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\GameController;

use function DI\autowire;

return [
    'ADMIN_ACTIONS' => [

    ],
    'ADMIN_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Playerlist::VIEW_IDENTIFIER => autowire(Playerlist::class),
    ]
];
