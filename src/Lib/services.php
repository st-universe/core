<?php

declare(strict_types=1);

namespace Stu\Lib;

use function DI\autowire;

return [
    UuidGeneratorInterface::class => autowire(UuidGenerator::class),
];
