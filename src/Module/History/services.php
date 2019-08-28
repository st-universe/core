<?php

declare(strict_types=1);

namespace Stu\Module\History;

use Stu\Control\ControllerTypeEnum;
use Stu\Control\GameController;
use Stu\Lib\SessionInterface;
use Stu\Module\History\View\Overview\Overview;
use Stu\Module\History\View\Overview\OverviewRequest;
use Stu\Module\History\View\Overview\OverviewRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    ControllerTypeEnum::TYPE_HISTORY => create(GameController::class)
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