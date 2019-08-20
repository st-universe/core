<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Research\Action\CancelResearch\CancelResearch;
use Stu\Module\Research\Action\StartResearch\StartResearch;
use Stu\Module\Research\Action\StartResearch\StartResearchRequest;
use Stu\Module\Research\Action\StartResearch\StartResearchRequestInterface;
use Stu\Module\Research\View\Overview\Overview;
use Stu\Module\Research\View\ShowResearch\ShowResearch;
use Stu\Module\Research\View\ShowResearch\ShowResearchRequest;
use Stu\Module\Research\View\ShowResearch\ShowResearchRequestInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    TalFactoryInterface::class => autowire(TalFactory::class),
    TechlistRetrieverInterface::class => autowire(TechlistRetriever::class),
    ShowResearchRequestInterface::class => autowire(ShowResearchRequest::class),
    StartResearchRequestInterface::class => autowire(StartResearchRequest::class),
    IntermediateController::TYPE_RESEARCH => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            [
                CancelResearch::ACTION_IDENTIFIER => autowire(CancelResearch::class),
                StartResearch::ACTION_IDENTIFIER => autowire(StartResearch::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowResearch::VIEW_IDENTIFIER => autowire(ShowResearch::class),
            ]
        ),
];