<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile;

use Stu\Module\Control\GameController;
use Stu\Module\PlayerProfile\View\Overview\Overview;
use Stu\Module\PlayerProfile\View\Overview\OverviewRequest;
use function DI\autowire;

return [
    'PLAYER_PROFILE_ACTIONS' => [],
    'PLAYER_PROFILE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class)
            ->constructorParameter(
                'overviewRequest',
                autowire(OverviewRequest::class)
            )
            ->constructorParameter(
                'profileVisitorRegistration',
                autowire(Lib\ProfileVisitorRegistration::class)
            ),
    ],
];
