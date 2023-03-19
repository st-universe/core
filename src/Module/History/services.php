<?php

declare(strict_types=1);

namespace Stu\Module\History;

use Stu\Module\Control\GameController;
use Stu\Module\History\Lib\EntryCreator;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\History\View\Overview\Overview;
use Stu\Module\History\View\Overview\OverviewRequest;
use Stu\Module\History\View\Overview\OverviewRequestInterface;

use function DI\autowire;

return [
    EntryCreatorInterface::class => autowire(EntryCreator::class),
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    'HISTORY_ACTIONS' => [],
    'HISTORY_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
    ]
];
