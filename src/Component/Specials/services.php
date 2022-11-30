<?php

declare(strict_types=1);

namespace Stu\Component\Specials;

use function DI\autowire;

return [
    AdventCycleInterface::class => autowire(AdventCycle::class)
];
