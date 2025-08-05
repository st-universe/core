<?php

declare(strict_types=1);

namespace Stu\Component\Database;

use function DI\autowire;

return [
    AchievementManagerInterface::class => autowire(AchievementManager::class)
];
