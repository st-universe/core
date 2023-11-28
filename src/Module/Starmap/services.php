<?php

declare(strict_types=1);

namespace Stu\Module\Starmap;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Starmap\Lib\StarmapUiFactory;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPosition;
use Stu\Module\Starmap\View\ShowSection\ShowSection;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequest;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\StarsystemGenerator\StarsystemGenerator;
use Stu\StarsystemGenerator\StarsystemGeneratorInterface;

use function DI\autowire;

return [
    StarsystemGeneratorInterface::class => autowire(StarsystemGenerator::class),
    ShowSectionRequestInterface::class => autowire(ShowSectionRequest::class),
    'STARMAP_ACTIONS' => [],
    'STARMAP_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowSection::VIEW_IDENTIFIER => autowire(ShowSection::class),
        ShowByPosition::VIEW_IDENTIFIER => autowire(ShowByPosition::class),
        RefreshSection::VIEW_IDENTIFIER => autowire(RefreshSection::class)
    ],
    StarmapUiFactoryInterface::class => autowire(StarmapUiFactory::class),
];
