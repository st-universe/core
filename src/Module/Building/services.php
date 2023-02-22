<?php

declare(strict_types=1);

namespace Stu\Module\Colony;

use Stu\Module\Building\Action\BuildingFunctionActionMapper;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;

use function DI\autowire;

return [
    BuildingFunctionActionMapperInterface::class => autowire(BuildingFunctionActionMapper::class),
];
