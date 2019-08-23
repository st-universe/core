<?php

declare(strict_types=1);

namespace Stu\Module\Starmap;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Starmap\View\Overview\Overview;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPosition;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPositionRequest;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPositionRequestInterface;
use Stu\Module\Starmap\View\ShowSection\ShowSection;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequest;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    ShowSectionRequestInterface::class => autowire(ShowSectionRequest::class),
    ShowByPositionRequestInterface::class => autowire(ShowByPositionRequest::class),
    IntermediateController::TYPE_STARMAP => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowSection::VIEW_IDENTIFIER => autowire(ShowSection::class),
                ShowByPosition::VIEW_IDENTIFIER => autowire(ShowByPosition::class),
            ]
        ),
];