<?php

declare(strict_types=1);

namespace Stu\Module\History;

use Stu\Control\GameController;
use Stu\Module\History\View\Overview\Overview;
use Stu\Module\History\View\Overview\OverviewRequest;
use Stu\Module\History\View\Overview\OverviewRequestInterface;
use function DI\autowire;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    'HISTORY_ACTIONS' => [],
    'HISTORY_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
    ]
];