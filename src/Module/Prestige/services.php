<?php

declare(strict_types=1);

namespace Stu\Module\Prestige;

use Stu\Module\Prestige\Lib\CreatePrestigeLog;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;

use function DI\autowire;

return [
    CreatePrestigeLogInterface::class => autowire(CreatePrestigeLog::class),
    'PRESTIGE_ACTIONS' => [],
    'PRESTIGE_VIEWS' => []
];
