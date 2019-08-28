<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile;

use Stu\Control\ControllerTypeEnum;
use Stu\Control\GameController;
use Stu\Lib\SessionInterface;
use Stu\Module\PlayerProfile\View\Overview\Overview;
use Stu\Module\PlayerProfile\View\Overview\OverviewRequest;
use Stu\Module\PlayerProfile\View\Overview\OverviewRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    ControllerTypeEnum::TYPE_PLAYER_PROFILE => create(GameController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
            ]
        ),
];