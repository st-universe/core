<?php

declare(strict_types=1);

namespace Stu\Module\Prestige;

use Stu\Module\Prestige\Lib\CreatePrestigeLog;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Prestige\Lib\PrestigeCalculation;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;

use function DI\autowire;

return [
    PrestigeCalculationInterface::class => autowire(PrestigeCalculation::class),
    CreatePrestigeLogInterface::class => autowire(CreatePrestigeLog::class),
    'PRESTIGE_ACTIONS' => [],
    'PRESTIGE_VIEWS' => []
];
