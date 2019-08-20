<?php

declare(strict_types=1);

namespace Stu\Module\History;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\History\View\Overview\Overview;
use Stu\Module\History\View\Overview\OverviewRequest;
use Stu\Module\History\View\Overview\OverviewRequestInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    IntermediateController::TYPE_HISTORY => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            [
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
            ]
        ),
];