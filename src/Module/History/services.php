<?php

declare(strict_types=1);

namespace Stu\Module\History;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\History\Lib\EntryCreator;
use Stu\Module\History\Lib\EntryCreatorInterface;

use function DI\autowire;

return [
    EntryCreatorInterface::class => autowire(EntryCreator::class),
    'HISTORY_ACTIONS' => [],
    'HISTORY_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
    ]
];
