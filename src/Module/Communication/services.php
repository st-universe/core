<?php

declare(strict_types=1);

namespace Stu\Module\Communication;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Communication\Action\SetKnMark\SetKnMark;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequest;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequestInterface;
use Stu\Module\Communication\View\Overview\Overview;
use Stu\Module\Communication\View\Overview\OverviewRequest;
use Stu\Module\Communication\View\Overview\OverviewRequestInterface;
use Stu\Module\Communication\View\ShowNewPm\ShowNewPm;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    SetKnMarkRequestInterface::class => autowire(SetKnMarkRequest::class),
    IntermediateController::TYPE_COMMUNICATION => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                SetKnMark::ACTION_IDENTIFIER => autowire(SetKnMark::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowNewPm::VIEW_IDENTIFIER => autowire(ShowNewPm::class),
            ]
        ),
];