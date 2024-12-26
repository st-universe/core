<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use function DI\autowire;

return [
    OrbitShipWrappersRetrieverInterface::class => autowire(OrbitShipWrappersRetriever::class),
    ColonyFunctionManagerInterface::class => autowire(ColonyFunctionManager::class),
    ColonyCreationInterface::class => autowire(ColonyCreation::class),
];
