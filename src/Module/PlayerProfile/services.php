<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistration;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;

use function DI\autowire;

return [
    ProfileVisitorRegistrationInterface::class => autowire(ProfileVisitorRegistration::class),
    'PLAYER_PROFILE_ACTIONS' => [],
    'PLAYER_PROFILE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class)
    ],
];
