<?php

declare(strict_types=1);

namespace Stu\Module\Colony;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Colony\Action\Abandon\Abandon;
use Stu\Module\Colony\Action\Abandon\AbandonRequest;
use Stu\Module\Colony\Action\Abandon\AbandonRequestInterface;
use Stu\Module\Colony\View\Overview\Overview;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    AbandonRequestInterface::class => autowire(AbandonRequest::class),
    IntermediateController::TYPE_COLONY_LIST => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                Abandon::ACTION_IDENTIFIER => autowire(Abandon::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
            ]
        ),
];