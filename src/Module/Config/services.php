<?php

declare(strict_types=1);

namespace Stu\Module\Config;

use function DI\autowire;

return [
    StuConfigInterface::class => autowire(StuConfig::class)
];
