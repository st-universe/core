<?php

declare(strict_types=1);

namespace Stu\Module\Station;

use Stu\Module\Control\GameController;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Station\View\Overview\Overview;
use Stu\Module\Station\View\ShowStationCosts\ShowStationCosts;
use Stu\Module\Station\View\ShowStationInfo\ShowStationInfo;

use function DI\autowire;

return [
    'STATION_ACTIONS' => [],
    'STATION_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Overview::VIEW_IDENTIFIER => autowire(Overview::class),
        ShowShip::VIEW_IDENTIFIER => autowire(ShowShip::class),
        ShowStationCosts::VIEW_IDENTIFIER => autowire(ShowStationCosts::class),
        ShowStationInfo::VIEW_IDENTIFIER => autowire(ShowStationInfo::class)
    ],
];
