<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use function DI\autowire;

return [
    CrewCountRetrieverInterface::class => autowire(CrewCountRetriever::class),
];
