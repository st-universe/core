<?php

declare(strict_types=1);

namespace Stu\Module\Starmap;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Starmap\Action\ScanUserStarmap\ScanUserStarmap;
use Stu\Module\Starmap\Lib\StarmapRelations;
use Stu\Module\Starmap\Lib\StarmapSensorCoverage;
use Stu\Module\Starmap\Lib\StarmapSensorData;
use Stu\Module\Starmap\Lib\StarmapUiFactory;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPosition;
use Stu\Module\Starmap\View\ShowSection\ShowSection;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequest;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Module\Starmap\View\ShowUserStarmapData\ShowUserStarmapData;
use Stu\Module\Starmap\View\ShowUserStarmapImage\ShowUserStarmapImage;
use Stu\Module\Starmap\View\ShowUserStarmapRealtime\ShowUserStarmapRealtime;
use Stu\Module\Starmap\View\ShowUserStarmapScan\ShowUserStarmapScan;
use Stu\StarsystemGenerator\StarsystemGenerator;
use Stu\StarsystemGenerator\StarsystemGeneratorInterface;

use function DI\autowire;

return [
    StarsystemGeneratorInterface::class => autowire(StarsystemGenerator::class),
    ShowSectionRequestInterface::class => autowire(ShowSectionRequest::class),
    'STARMAP_ACTIONS' => [
        ScanUserStarmap::ACTION_IDENTIFIER => autowire(ScanUserStarmap::class)
    ],
    'STARMAP_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowSection::VIEW_IDENTIFIER => autowire(ShowSection::class),
        ShowByPosition::VIEW_IDENTIFIER => autowire(ShowByPosition::class),
        RefreshSection::VIEW_IDENTIFIER => autowire(RefreshSection::class),
        ShowUserStarmapImage::VIEW_IDENTIFIER => autowire(ShowUserStarmapImage::class),
        ShowUserStarmapData::VIEW_IDENTIFIER => autowire(ShowUserStarmapData::class),
        ShowUserStarmapRealtime::VIEW_IDENTIFIER => autowire(ShowUserStarmapRealtime::class),
        ShowUserStarmapScan::VIEW_IDENTIFIER => autowire(ShowUserStarmapScan::class)
    ],
    StarmapUiFactoryInterface::class => autowire(StarmapUiFactory::class),
    StarmapRelations::class => autowire(StarmapRelations::class),
    StarmapSensorCoverage::class => autowire(StarmapSensorCoverage::class),
    StarmapSensorData::class => autowire(StarmapSensorData::class),
];
