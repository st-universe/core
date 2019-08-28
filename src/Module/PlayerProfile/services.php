<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile;

use Stu\Control\GameController;
use Stu\Module\PlayerProfile\View\Overview\Overview;
use Stu\Module\PlayerProfile\View\Overview\OverviewRequest;
use Stu\Module\PlayerProfile\View\Overview\OverviewRequestInterface;
use function DI\autowire;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    'PLAYER_PROFILE_ACTIONS' => [],
    'PLAYER_PROFILE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
    ],
];