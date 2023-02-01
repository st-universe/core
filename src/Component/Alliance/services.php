<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use function DI\autowire;

return [
    AllianceUserApplicationCheckerInterface::class => autowire(AllianceUserApplicationChecker::class),
];
