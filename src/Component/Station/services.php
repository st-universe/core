<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use function DI\autowire;

return [
    StationUtilityInterface::class => autowire(StationUtility::class),
];
