<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use function DI\autowire;

return [
    RefactorRunner::class => autowire(RefactorRunner::class)
];
