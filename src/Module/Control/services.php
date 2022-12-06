<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use function DI\autowire;

return [
    SemaphoreUtilInterface::class => autowire(SemaphoreUtil::class),
    StuTime::class => autowire(StuTime::class),
    StuHashInterface::class => autowire(StuHash::class)
];
