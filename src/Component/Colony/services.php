<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManager;
use function DI\autowire;

return [
    ColonyStorageManagerInterface::class => autowire(ColonyStorageManager::class),
    OrbitShipListRetrieverInterface::class => autowire(OrbitShipListRetriever::class),
];
