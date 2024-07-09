<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Research\Action\CancelResearch\CancelResearch;
use Stu\Module\Research\Action\StartResearch\StartResearch;
use Stu\Module\Research\Action\StartResearch\StartResearchRequest;
use Stu\Module\Research\Action\StartResearch\StartResearchRequestInterface;
use Stu\Module\Research\View\ShowResearch\ShowResearch;
use Stu\Module\Research\View\ShowResearch\ShowResearchRequest;
use Stu\Module\Research\View\ShowResearch\ShowResearchRequestInterface;

use function DI\autowire;

return [
    SelectedTechFactoryInterface::class => autowire(SelectedTechFactory::class),
    TechlistRetrieverInterface::class => autowire(TechlistRetriever::class),
    ShowResearchRequestInterface::class => autowire(ShowResearchRequest::class),
    StartResearchRequestInterface::class => autowire(StartResearchRequest::class),
    ResearchStateFactoryInterface::class => autowire(ResearchStateFactory::class),
    'RESEARCH_ACTIONS' => [
        CancelResearch::ACTION_IDENTIFIER => autowire(CancelResearch::class),
        StartResearch::ACTION_IDENTIFIER => autowire(StartResearch::class),
    ],
    'RESEARCH_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowResearch::VIEW_IDENTIFIER => autowire(ShowResearch::class),
    ],
];
