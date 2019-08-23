<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Maindesk\Action\FirstColony\FirstColony;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequest;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequestInterface;
use Stu\Module\Maindesk\View\Overview\Overview;
use Stu\Module\Maindesk\View\ShowColonyList\ShowColonyList;
use Stu\Module\Maindesk\View\ShowColonyListAjax\ShowColonyListAjax;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    FirstColonyRequestInterface::class => autowire(FirstColonyRequest::class),
    IntermediateController::TYPE_MAINDESK => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                FirstColony::ACTION_IDENTIFIER => autowire(FirstColony::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowColonyList::VIEW_IDENTIFIER => autowire(ShowColonyList::class),
                ShowColonyListAjax::VIEW_IDENTIFIER => autowire(ShowColonyListAjax::class),
            ]
        ),
];