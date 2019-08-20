<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use function DI\autowire;

return [
    TalFactoryInterface::class => autowire(TalFactory::class),
];