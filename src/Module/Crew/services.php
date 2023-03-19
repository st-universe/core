<?php

declare(strict_types=1);

namespace Stu\Module\Crew;

use Stu\Module\Crew\Lib\CrewCreator;
use Stu\Module\Crew\Lib\CrewCreatorInterface;

use function DI\autowire;

return [
    CrewCreatorInterface::class => autowire(CrewCreator::class),
];
