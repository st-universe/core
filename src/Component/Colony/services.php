<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use function DI\autowire;

return [
    Storage\ColonyStorageManagerInterface::class => autowire(Storage\ColonyStorageManager::class)
];
